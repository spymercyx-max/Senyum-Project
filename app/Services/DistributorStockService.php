<?php

namespace App\Services;

use App\Models\DistributorStock;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Stok milik distributor per produk.
 * + bertambah HANYA saat PO disetujui (alokasi dari stok pusat)
 * - berkurang HANYA saat distributor mencatat penjualan
 * Tidak pernah mentransfer seluruh stok; selalu sejumlah qty.
 */
class DistributorStockService
{
    public function available(User $distributor, Product $product): int
    {
        return (int) (DistributorStock::where('distributor_id', $distributor->id)
            ->where('product_id', $product->id)
            ->value('qty') ?? 0);
    }

    /** @return array<int, array{product: Product, qty: int}> */
    public function allFor(User $distributor): array
    {
        $rows = DistributorStock::with('product')
            ->where('distributor_id', $distributor->id)
            ->get();

        return $rows->map(fn (DistributorStock $r) => [
            'product' => $r->product,
            'qty' => (int) $r->qty,
        ])->all();
    }

    public function totalUnits(User $distributor): int
    {
        return (int) DistributorStock::where('distributor_id', $distributor->id)->sum('qty');
    }

    /** Alokasi dari stok pusat ke distributor (dipanggil saat PO disetujui). */
    public function allocate(User $distributor, Product $product, int $qty, ?string $reason = null): void
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Qty alokasi minimal 1.');
        }

        DB::transaction(function () use ($distributor, $product, $qty) {
            $row = DistributorStock::where('distributor_id', $distributor->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->increment('qty', $qty);
            } else {
                DistributorStock::create([
                    'distributor_id' => $distributor->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                ]);
            }
        });
    }

    /** Pemakaian saat penjualan. Menolak bila stok tidak cukup (anti negatif). */
    public function deduct(User $distributor, Product $product, int $qty): void
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Qty penjualan minimal 1.');
        }

        DB::transaction(function () use ($distributor, $product, $qty) {
            $row = DistributorStock::where('distributor_id', $distributor->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $have = (int) ($row?->qty ?? 0);

            if ($have < $qty) {
                throw new InvalidArgumentException(
                    "Stok {$product->name} tidak mencukupi (tersedia {$have}, diminta {$qty}). Buat PO dulu untuk menambah stok."
                );
            }

            $row->decrement('qty', $qty);
        });
    }
}
