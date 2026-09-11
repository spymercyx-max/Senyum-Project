<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PurchaseOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function actors(): array
    {
        $dev = User::factory()->developer()->create();
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $dist->distributorProfile()->create(['status' => 'approved']);
        return [$dev, $dist];
    }

    private function stockedProduct(int $stock = 1000): Product
    {
        $p = Product::factory()->create(['status' => 'active', 'price' => 12000, 'distributor_price' => 10000]);
        Inventory::create(['product_id' => $p->id, 'stock' => $stock, 'reserved' => 0, 'threshold' => 10]);
        return $p;
    }

    public function test_delivery_flow_with_exact_inventory_movement(): void
    {
        [$dev, $dist] = $this->actors();
        $p = $this->stockedProduct(1000);
        $svc = app(PurchaseOrderService::class);

        $po = $svc->createOrder($dist, [
            ['product_id' => $p->id, 'qty' => 40],
        ], ['fulfillment' => 'delivery', 'payment_proof' => 'payment-proofs/x.jpg']);

        $this->assertSame('draft', $po->status);
        $this->assertSame(40 * 10000, $po->total_amount); // harga distributor default
        $this->assertSame(1000, $p->inventory()->first()->stock); // belum bergerak

        $po = $svc->transition($po, 'disetujui', $dev);
        $this->assertSame('disetujui', $po->status);
        $this->assertSame(960, $p->inventory()->first()->stock); // hanya 40 yang pindah
        $this->assertSame(40, \App\Models\DistributorStock::where('distributor_id', $dist->id)->where('product_id', $p->id)->value('qty'));

        // Resi wajib untuk DIKIRIM.
        try {
            $svc->transition($po, 'dikirim', $dev);
            $this->fail('Tanpa resi harus ditolak.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $po = $svc->transition($po, 'dikirim', $dev, ['tracking_number' => 'JNE-123']);
        $po = $svc->transition($po, 'selesai', $dev);

        $this->assertSame('selesai', $po->status);
        $this->assertSame(960, $p->inventory()->first()->stock); // tidak berkurang lagi
        $this->assertGreaterThanOrEqual(4, $po->histories()->count()); // draft→setuju→kirim→selesai
        $this->assertDatabaseHas('notifications', ['user_id' => $dist->id, 'type' => 'po_shipped']);
    }

    public function test_pickup_flow(): void
    {
        [$dev, $dist] = $this->actors();
        $p = $this->stockedProduct(100);
        $svc = app(PurchaseOrderService::class);

        // Pickup tanpa bukti bayar tetap boleh.
        $po = $svc->createOrder($dist, [['product_id' => $p->id, 'qty' => 10]], ['fulfillment' => 'pickup']);
        $po = $svc->transition($po, 'disetujui', $dev);
        $po = $svc->transition($po, 'diambil', $dev, ['pickup_message' => 'Ambil di gudang A.']);
        $po = $svc->transition($po, 'selesai', $dev);

        $this->assertSame('selesai', $po->status);
        $this->assertSame('Ambil di gudang A.', $po->pickup_message);
        $this->assertSame(90, $p->inventory()->first()->stock);
    }

    public function test_rejection_requires_reason_and_keeps_history(): void
    {
        [$dev, $dist] = $this->actors();
        $p = $this->stockedProduct(100);
        $svc = app(PurchaseOrderService::class);

        $po = $svc->createOrder($dist, [['product_id' => $p->id, 'qty' => 5]], ['fulfillment' => 'pickup']);

        try {
            $svc->transition($po, 'ditolak', $dev);
            $this->fail('Tanpa alasan harus ditolak.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $po = $svc->transition($po, 'ditolak', $dev, ['rejection_reason' => 'Stok pusat kosong.']);
        $this->assertSame('ditolak', $po->status);
        $this->assertSame(100, $p->inventory()->first()->stock); // stok utuh
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id]); // riwayat tetap ada
    }

    public function test_invalid_jumps_rejected_and_double_approval_safe(): void
    {
        [$dev, $dist] = $this->actors();
        $p = $this->stockedProduct(100);
        $svc = app(PurchaseOrderService::class);

        $po = $svc->createOrder($dist, [['product_id' => $p->id, 'qty' => 10]], ['fulfillment' => 'delivery', 'payment_proof' => 'x.jpg']);

        try {
            $svc->transition($po, 'selesai', $dev);
            $this->fail('Lompat draft→selesai harus ditolak.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $svc->transition($po, 'disetujui', $dev);
        try {
            $svc->transition($po, 'disetujui', $dev); // klik ganda
            $this->fail('Approve ganda harus ditolak.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }
        $this->assertSame(90, $p->inventory()->first()->stock); // tepat sekali
    }

    public function test_delivery_requires_payment_proof(): void
    {
        [, $dist] = $this->actors();
        $p = $this->stockedProduct(100);

        try {
            app(PurchaseOrderService::class)->createOrder($dist, [['product_id' => $p->id, 'qty' => 2]], ['fulfillment' => 'delivery']);
            $this->fail('Tanpa bukti bayar harus ditolak.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Bukti pembayaran', $e->getMessage());
        }
    }

    public function test_insufficient_central_stock_blocks_approval(): void
    {
        [$dev, $dist] = $this->actors();
        $p = $this->stockedProduct(5);
        $svc = app(PurchaseOrderService::class);

        $po = $svc->createOrder($dist, [['product_id' => $p->id, 'qty' => 50]], ['fulfillment' => 'pickup']);

        try {
            $svc->transition($po, 'disetujui', $dev);
            $this->fail('Stok kurang harus menggagalkan approval.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }
        $this->assertSame('draft', $po->refresh()->status);
        $this->assertSame(5, $p->inventory()->first()->stock);
    }
}
