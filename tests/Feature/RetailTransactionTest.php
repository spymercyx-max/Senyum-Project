<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Territory;
use App\Models\User;
use App\Services\DistributorStockService;
use App\Services\PurchaseOrderService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class RetailTransactionTest extends TestCase
{
    use RefreshDatabase;

    private function setupFlow(): array
    {
        $dev = User::factory()->developer()->create();
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $dist->distributorProfile()->create(['status' => 'approved']);

        $territory = Territory::factory()->create();
        $outlet = Outlet::factory()->create([
            'territory_id' => $territory->id,
            'distributor_id' => $dist->id,
            'status' => 'active',
        ]);

        $a = Product::factory()->create(['status' => 'active', 'price' => 12000, 'distributor_price' => 10000]);
        $b = Product::factory()->create(['status' => 'active', 'price' => 20000, 'distributor_price' => 18000]);
        foreach ([$a, $b] as $p) {
            Inventory::create(['product_id' => $p->id, 'stock' => 1000, 'reserved' => 0, 'threshold' => 10]);
        }

        // Pasok via PO disetujui: A=40, B=10.
        $po = app(PurchaseOrderService::class)->createOrder($dist, [
            ['product_id' => $a->id, 'qty' => 40],
            ['product_id' => $b->id, 'qty' => 10],
        ], ['fulfillment' => 'pickup']);
        app(PurchaseOrderService::class)->transition($po, 'disetujui', $dev);

        return [$dist, $outlet, $a, $b];
    }

    public function test_retail_multi_item_deducts_distributor_stock_only(): void
    {
        [$dist, $outlet, $a, $b] = $this->setupFlow();

        $trx = app(TransactionService::class)->createTransaction($dist, $outlet, [
            ['product_id' => $a->id, 'qty' => 2],
            ['product_id' => $b->id, 'qty' => 4],
        ], ['type' => 'retail', 'buyer_name' => 'Pak Budi', 'sold_at' => now()]);

        $this->assertSame('retail', $trx->type);
        $this->assertSame('Pak Budi', $trx->buyer_name);
        $this->assertSame(2 * 12000 + 4 * 20000, $trx->total_amount);
        $this->assertSame(2, $trx->items()->count());
        // Snapshot produk tersimpan.
        $this->assertSame($a->name, $trx->items()->where('product_id', $a->id)->value('product_name'));

        $stocks = app(DistributorStockService::class);
        $this->assertSame(38, $stocks->available($dist, $a));
        $this->assertSame(6, $stocks->available($dist, $b));

        // Stok pusat tidak tersentuh penjualan ecer (sudah pindah saat approval).
        $this->assertSame(960, $a->inventory()->first()->stock);
        $this->assertSame(990, $b->inventory()->first()->stock);
    }

    public function test_retail_blocked_when_distributor_stock_insufficient(): void
    {
        [$dist, $outlet, $a] = $this->setupFlow();

        try {
            app(TransactionService::class)->createTransaction($dist, $outlet, [
                ['product_id' => $a->id, 'qty' => 999],
            ], ['type' => 'retail']);
            $this->fail('Harus ditolak karena stok distributor kurang.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('tidak mencukupi', $e->getMessage());
        }
    }
}
