<?php

namespace App\Services;

use App\Enums\PoFulfillment;
use App\Enums\PurchaseOrderStatus;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * SATU-SATUNYA otoritas transisi status PO (§92).
 * Controller/Blade/Livewire dilarang menduplikasi peta transisi —
 * pakai PurchaseOrderStatus::allowedNext() untuk visibilitas tombol
 * dan service ini untuk eksekusi.
 */
class PurchaseOrderService
{
    public function __construct(
        protected InventoryService $inventory,
        protected DistributorStockService $distributorStock,
        protected ProductPricingService $pricing,
    ) {}

    /**
     * @param  array<int, array{product_id:int, qty:int, price?:int|null}>  $items
     * @param  array{fulfillment?:string, order_date?:mixed, payment_proof?:?string, notes?:?string, code?:string} $attributes
     */
    public function createOrder(User $distributor, array $items, array $attributes = []): PurchaseOrder
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Item purchase order tidak boleh kosong.');
        }

        $fulfillment = $attributes['fulfillment'] ?? PoFulfillment::Delivery->value;
        if (! in_array($fulfillment, PoFulfillment::values(), true)) {
            throw new InvalidArgumentException('Mode pemenuhan tidak dikenal.');
        }

        $paymentProof = $attributes['payment_proof'] ?? null;
        if ($fulfillment === PoFulfillment::Delivery->value && ! $paymentProof) {
            throw new InvalidArgumentException('Bukti pembayaran wajib diunggah untuk PO DIKIRIM.');
        }

        $orderDate = $attributes['order_date'] ?? now()->toDateString();
        try {
            $orderDate = \Carbon\Carbon::parse($orderDate)->toDateString();
        } catch (\Throwable) {
            throw new InvalidArgumentException('Tanggal pemesanan tidak valid.');
        }

        return DB::transaction(function () use ($distributor, $items, $attributes, $fulfillment, $paymentProof, $orderDate) {
            $order = PurchaseOrder::create([
                'code' => $attributes['code'] ?? $this->generateCode(),
                'distributor_id' => $distributor->id,
                'status' => PurchaseOrderStatus::Draft->value,
                'fulfillment' => $fulfillment,
                'order_date' => $orderDate,
                'payment_proof' => $paymentProof,
                'notes' => $attributes['notes'] ?? null,
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($items as $row) {
                $product = Product::findOrFail($row['product_id']);

                if ($product->status !== 'active') {
                    throw new InvalidArgumentException("Produk {$product->name} sedang tidak aktif.");
                }

                $qty = (int) ($row['qty'] ?? 0);
                if ($qty < 1) {
                    throw new InvalidArgumentException('Qty item minimal 1.');
                }

                $price = isset($row['price']) && $row['price'] !== null && $row['price'] !== ''
                    ? (int) $row['price']
                    : $this->pricing->resolve($product, 'distributor', $qty);

                if (! isset($row['price']) || $row['price'] === null || $row['price'] === '') {
                    if ($price <= 0) {
                        throw new InvalidArgumentException(
                            "Harga distributor untuk {$product->name} belum diatur developer."
                        );
                    }
                }

                if ($price < 0) {
                    throw new InvalidArgumentException('Harga item tidak boleh negatif.');
                }

                $subtotal = $qty * $price;
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total_amount' => $total]);
            $this->recordHistory($order, null, PurchaseOrderStatus::Draft->value, $distributor, 'PO dibuat distributor.');

            ActivityLog::create([
                'actor_id' => $distributor->id,
                'action' => 'purchase_order.created',
                'entity' => PurchaseOrder::class,
                'entity_id' => $order->id,
                'metadata' => ['code' => $order->code, 'total' => $total, 'fulfillment' => $fulfillment],
                'ip' => request()->ip(),
            ]);

            Notification::create([
                'user_id' => $this->firstDeveloperId(),
                'type' => 'po_submitted',
                'title' => 'PO baru: '.$order->code,
                'body' => $distributor->name.' mengajukan PO '.($fulfillment === 'delivery' ? 'DIKIRIM' : 'DIAMBIL').' senilai Rp'.number_format($total, 0, ',', '.'),
                'data' => ['purchase_order_id' => $order->id],
            ]);

            return $order->refresh()->load('items');
        });
    }

    /**
     * Eksekusi transisi status. Idempoten terhadap klik ganda:
     * transisi kedua dari status yang sama DITOLAK sebagai invalid
     * sebelum efek samping apa pun terjadi.
     *
     * @param  array{tracking_number?:?string, delivery_note?:?string, pickup_message?:?string, rejection_reason?:?string, note?:?string} $params
     */
    public function transition(PurchaseOrder $order, string $to, ?User $actor = null, array $params = []): PurchaseOrder
    {
        if (! in_array($to, PurchaseOrderStatus::values(), true)) {
            throw new InvalidArgumentException("Status tujuan [{$to}] tidak dikenal.");
        }

        return DB::transaction(function () use ($order, $to, $actor, $params) {
            $locked = PurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;

            if (! PurchaseOrderStatus::can($from, $to, $locked->fulfillment)) {
                throw new InvalidArgumentException("Transisi status {$from} → {$to} tidak diizinkan.");
            }

            $note = $params['note'] ?? null;

            match ($to) {
                PurchaseOrderStatus::Disetujui->value => $this->applyApproval($locked, $actor),
                PurchaseOrderStatus::Ditolak->value => $this->applyRejection($locked, $params),
                PurchaseOrderStatus::Dikirim->value => $this->applyShipment($locked, $params),
                PurchaseOrderStatus::Diambil->value => $this->applyPickup($locked, $params),
                PurchaseOrderStatus::Selesai->value => $locked->fill([]),
                default => throw new InvalidArgumentException("Transisi ke [{$to}] tidak didukung."),
            };

            $locked->status = $to;
            $locked->save();

            $this->recordHistory($locked, $from, $to, $actor, $note);
            $this->notifyTransition($locked, $from, $to, $note);

            ActivityLog::create([
                'actor_id' => $actor?->id,
                'action' => 'purchase_order.status_changed',
                'entity' => PurchaseOrder::class,
                'entity_id' => $locked->id,
                'metadata' => ['from' => $from, 'to' => $to, 'code' => $locked->code],
                'ip' => request()->ip(),
            ]);

            return $locked->refresh()->load(['items', 'histories']);
        });
    }

    /**
     * Approval: HANYA qty yang disetujui yang pindah dari stok pusat
     * ke stok distributor. Dikunci baris + cek cukup, anti negatif,
     * anti deduksi ganda (hanya dari DRAFT, satu transaksi DB).
     */
    protected function applyApproval(PurchaseOrder $order, ?User $actor): void
    {
        foreach ($order->items()->with('product')->get() as $item) {
            $product = $item->product;
            if (! $product) {
                throw new InvalidArgumentException("Produk item #{$item->id} tidak ditemukan.");
            }

            $this->inventory->deductLocked($product, (int) $item->qty, 'po:'.$order->code);
            $this->distributorStock->allocate($order->distributor, $product, (int) $item->qty);
        }
    }

    protected function applyRejection(PurchaseOrder $order, array $params): void
    {
        $reason = trim((string) ($params['rejection_reason'] ?? $params['note'] ?? ''));
        if ($reason === '') {
            throw new InvalidArgumentException('Alasan penolakan wajib diisi.');
        }
        $order->rejection_reason = $reason;
    }

    protected function applyShipment(PurchaseOrder $order, array $params): void
    {
        $resi = trim((string) ($params['tracking_number'] ?? ''));
        if ($resi === '') {
            throw new InvalidArgumentException('Nomor resi wajib diisi untuk pengiriman.');
        }
        $order->tracking_number = $resi;
        $order->delivery_note = $params['delivery_note'] ?? null;
    }

    protected function applyPickup(PurchaseOrder $order, array $params): void
    {
        $order->pickup_message = $params['pickup_message'] ?? null;
    }

    protected function recordHistory(PurchaseOrder $order, ?string $from, string $to, ?User $actor, ?string $note): void
    {
        $order->histories()->create([
            'from' => $from,
            'to' => $to,
            'actor_id' => $actor?->id,
            'note' => $note,
        ]);
    }

    protected function notifyTransition(PurchaseOrder $order, string $from, string $to, ?string $note): void
    {
        $map = [
            PurchaseOrderStatus::Disetujui->value => ['po_approved', "PO {$order->code} DISETUJUI", 'Pesananmu disetujui dan stok dialokasikan.'],
            PurchaseOrderStatus::Ditolak->value => ['po_rejected', "PO {$order->code} DITOLAK", 'Alasan: '.($order->rejection_reason ?? '-')],
            PurchaseOrderStatus::Dikirim->value => ['po_shipped', "PO {$order->code} DIKIRIM", 'Resi: '.($order->tracking_number ?? '-')],
            PurchaseOrderStatus::Diambil->value => ['po_pickup', "PO {$order->code} SIAP DIAMBIL", $order->pickup_message ?? 'Silakan ambil sesuai jam operasional.'],
            PurchaseOrderStatus::Selesai->value => ['po_completed', "PO {$order->code} SELESAI", 'Terima kasih.'],
        ];

        if (! isset($map[$to])) {
            return;
        }

        [$type, $title, $body] = $map[$to];

        Notification::create([
            'user_id' => $order->distributor_id,
            'type' => $type,
            'title' => $title,
            'body' => $body.($note ? ' Catatan: '.$note : ''),
            'data' => ['purchase_order_id' => $order->id, 'from' => $from, 'to' => $to],
        ]);
    }

    protected function firstDeveloperId(): ?int
    {
        return User::where('role', 'developer')->where('status', 'active')->value('id');
    }

    public function generateCode(): string
    {
        return 'PO-'.now()->format('Ymd-His').'-'.strtoupper(substr(uniqid(), -4));
    }
}
