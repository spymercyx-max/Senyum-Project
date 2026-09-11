<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransactionService
{
    public function __construct(
        protected DistributorStockService $distributorStock,
        protected ProductPricingService $pricing,
    ) {}

    /**
     * @param  array<int, array{product_id:int, qty:int, price?:int|null}>  $items
     * @param  array{code?:string, status?:string, type?:string, notes?:?string, buyer_name?:?string, sold_at?:mixed} $attributes
     */
    public function createTransaction(User $distributor, Outlet $outlet, array $items, array $attributes = []): Transaction
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Item transaksi tidak boleh kosong.');
        }

        $this->assertOutletAccessible($distributor, $outlet);

        $type = $attributes['type'] ?? 'outlet';
        if (! in_array($type, ['outlet', 'retail'], true)) {
            throw new InvalidArgumentException('Tipe transaksi tidak dikenal.');
        }

        return DB::transaction(function () use ($distributor, $outlet, $items, $attributes, $type) {
            $transaction = Transaction::create([
                'code' => $attributes['code'] ?? $this->generateCode($type),
                'distributor_id' => $distributor->id,
                'outlet_id' => $outlet->id,
                'buyer_name' => $attributes['buyer_name'] ?? null,
                'status' => $attributes['status'] ?? 'completed',
                'type' => $type,
                'total_amount' => 0,
                'notes' => $attributes['notes'] ?? null,
                'sold_at' => $attributes['sold_at'] ?? now(),
            ]);

            $total = 0;

            foreach ($items as $row) {
                $line = $this->prepareLine($row, $type);
                $subtotal = $line['qty'] * $line['price'];
                $total += $subtotal;

                // Hanya qty terjual yang berkurang — dari stok distributor,
                // bukan dari stok pusat (stok pusat sudah pindah saat PO disetujui).
                $this->distributorStock->deduct($distributor, $line['product'], $line['qty']);

                $transaction->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'qty' => $line['qty'],
                    'price' => $line['price'],
                    'cost_price' => $line['cost_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $transaction->update(['total_amount' => $total]);

            ActivityLog::create([
                'actor_id' => $distributor->id,
                'action' => $type === 'retail' ? 'retail.created' : 'transaction.created',
                'entity' => Transaction::class,
                'entity_id' => $transaction->id,
                'metadata' => ['code' => $transaction->code, 'total' => $total, 'type' => $type],
                'ip' => request()->ip(),
            ]);

            return $transaction->refresh()->load('items');
        });
    }

    /**
     * Koreksi transaksi: ganti seluruh item sekaligus. Selisih qty per
     * produk dihitung (delta): kurang → kembalikan ke stok, lebih →
     * kurangi stok. Semua dalam satu DB transaction. Riwayat append-only
     * dicatat di ActivityLog (tidak bisa diedit/dihapus user).
     *
     * @param  array<int, array{product_id:int, qty:int, price?:int|null}>  $items
     * @param  array{buyer_name?:?string, sold_at?:mixed, notes?:?string, reason?:?string} $attributes
     */
    public function updateTransaction(User $actor, Transaction $transaction, array $items, array $attributes = []): Transaction
    {
        if ($transaction->status === 'void') {
            throw new InvalidArgumentException('Transaksi yang sudah dibatalkan tidak bisa diubah.');
        }
        if (empty($items)) {
            throw new InvalidArgumentException('Item transaksi tidak boleh kosong.');
        }

        return DB::transaction(function () use ($actor, $transaction, $items, $attributes) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $distributor = $locked->distributor;

            $oldLines = [];
            foreach ($locked->items as $item) {
                $oldLines[$item->product_id] = [
                    'qty' => (int) $item->qty,
                    'price' => (int) $item->price,
                    'subtotal' => (int) $item->subtotal,
                ];
            }
            $oldTotal = (int) $locked->total_amount;

            $newByProduct = [];
            foreach ($items as $row) {
                $line = $this->prepareLine($row, $locked->type);
                $pid = $line['product']->id;
                if (isset($newByProduct[$pid])) {
                    throw new InvalidArgumentException("Produk {$line['product']->name} duplikat dalam satu transaksi.");
                }
                $newByProduct[$pid] = $line;
            }

            $deltas = [];
            foreach ($newByProduct as $pid => $line) {
                $delta = $line['qty'] - (int) ($oldLines[$pid]['qty'] ?? 0);
                $deltas[$pid] = $delta;
                if ($delta > 0) {
                    $this->distributorStock->deduct($distributor, $line['product'], $delta);
                } elseif ($delta < 0) {
                    $this->distributorStock->allocate($distributor, $line['product'], -$delta);
                }
            }
            // Produk yang dihapus dari koreksi → seluruh qty lama kembali.
            foreach ($oldLines as $pid => $old) {
                if (! isset($newByProduct[$pid])) {
                    $product = Product::findOrFail($pid);
                    $this->distributorStock->allocate($distributor, $product, $old['qty']);
                    $deltas[$pid] = -$old['qty'];
                }
            }

            $locked->items()->delete();
            $total = 0;
            foreach ($newByProduct as $line) {
                $subtotal = $line['qty'] * $line['price'];
                $total += $subtotal;
                $locked->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'qty' => $line['qty'],
                    'price' => $line['price'],
                    'cost_price' => $line['cost_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $locked->fill([
                'buyer_name' => $attributes['buyer_name'] ?? $locked->buyer_name,
                'sold_at' => $attributes['sold_at'] ?? $locked->sold_at,
                'notes' => $attributes['notes'] ?? $locked->notes,
                'total_amount' => $total,
            ])->save();

            ActivityLog::create([
                'actor_id' => $actor->id,
                'action' => 'transaction.corrected',
                'entity' => Transaction::class,
                'entity_id' => $locked->id,
                'metadata' => [
                    'code' => $locked->code,
                    'old_total' => $oldTotal,
                    'new_total' => $total,
                    'old_items' => $oldLines,
                    'deltas' => $deltas,
                    'reason' => $attributes['reason'] ?? null,
                ],
                'ip' => request()->ip(),
            ]);

            return $locked->refresh()->load('items');
        });
    }

    /**
     * Pembatalan (void): kembalikan SELURUH qty ke stok distributor,
     * tandai status void + soft-delete (tetap tersimpan & dapat diaudit).
     * Bukan destroy permanen. Riwayat immutable di ActivityLog.
     */
    public function voidTransaction(User $actor, Transaction $transaction, ?string $reason = null): Transaction
    {
        if ($transaction->status === 'void') {
            throw new InvalidArgumentException('Transaksi ini sudah dibatalkan.');
        }

        return DB::transaction(function () use ($actor, $transaction, $reason) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $distributor = $locked->distributor;

            $restored = [];
            foreach ($locked->items()->with('product')->get() as $item) {
                if ($item->product) {
                    $this->distributorStock->allocate($distributor, $item->product, (int) $item->qty);
                }
                $restored[$item->product_id] = (int) $item->qty;
            }

            $locked->status = 'void';
            $locked->save();
            $locked->delete();

            ActivityLog::create([
                'actor_id' => $actor->id,
                'action' => 'transaction.voided',
                'entity' => Transaction::class,
                'entity_id' => $locked->id,
                'metadata' => [
                    'code' => $locked->code,
                    'old_total' => (int) $locked->total_amount,
                    'restored_qty' => $restored,
                    'reason' => $reason,
                ],
                'ip' => request()->ip(),
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Validasi + resolusi satu baris item. Harga jual ke outlet adalah
     * nilai per-transaksi (tidak menyentuh master produk). Bila harga
     * tidak diisi: eceran → kanal customer, ke outlet → kanal distributor.
     *
     * @return array{product: Product, qty: int, price: int, cost_price: int}
     */
    protected function prepareLine(array $row, string $type): array
    {
        $product = Product::findOrFail($row['product_id'] ?? 0);
        $qty = (int) ($row['qty'] ?? 0);

        if ($qty < 1) {
            throw new InvalidArgumentException('Qty item minimal 1.');
        }
        if ($product->status !== 'active' && ! $product->trashed()) {
            throw new InvalidArgumentException("Produk {$product->name} sedang tidak aktif.");
        }

        $given = $row['price'] ?? null;
        if ($given !== null && $given !== '') {
            $price = (int) $given;
            if ($price < 0) {
                throw new InvalidArgumentException('Harga item tidak boleh negatif.');
            }
        } else {
            $channel = $type === 'retail' ? 'customer' : 'distributor';
            $price = $this->pricing->resolve($product, $channel, $qty);
            if ($price <= 0) {
                throw new InvalidArgumentException(
                    "Harga {$channel} untuk {$product->name} belum diatur developer."
                );
            }
        }

        $cost = $this->pricing->resolve($product, 'distributor', $qty);

        return ['product' => $product, 'qty' => $qty, 'price' => $price, 'cost_price' => $cost];
    }

    protected function assertOutletAccessible(User $distributor, Outlet $outlet): void
    {
        if ($distributor->isDeveloper()) {
            return;
        }

        if ((int) $outlet->distributor_id === (int) $distributor->id) {
            return;
        }

        $distributorTerritoryId = $distributor->distributorProfile?->territory_id;

        if ($distributorTerritoryId && (int) $outlet->territory_id === (int) $distributorTerritoryId) {
            return;
        }

        throw new InvalidArgumentException('Outlet bukan milik distributor / wilayah berbeda.');
    }

    public function generateCode(string $type = 'outlet'): string
    {
        $prefix = $type === 'retail' ? 'ECR' : 'TRX';

        return $prefix.'-'.now()->format('Ymd-His').'-'.strtoupper(substr(uniqid(), -4));
    }
}
