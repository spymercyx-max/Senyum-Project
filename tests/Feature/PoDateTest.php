<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_arbitrary_business_dates_preserved(): void
    {
        $dev = User::factory()->developer()->create();
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $dist->distributorProfile()->create(['status' => 'approved']);
        $p = Product::factory()->create(['status' => 'active', 'price' => 12000, 'distributor_price' => 10000]);
        Inventory::create(['product_id' => $p->id, 'stock' => 1000, 'reserved' => 0, 'threshold' => 10]);
        $svc = app(PurchaseOrderService::class);

        $past = $svc->createOrder($dist, [['product_id' => $p->id, 'qty' => 1]], [
            'fulfillment' => 'pickup', 'order_date' => now()->subDays(40)->toDateString(),
        ]);
        $future = $svc->createOrder($dist, [['product_id' => $p->id, 'qty' => 1]], [
            'fulfillment' => 'pickup', 'order_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->assertSame(now()->subDays(40)->toDateString(), $past->order_date->toDateString());
        $this->assertSame(now()->addDays(10)->toDateString(), $future->order_date->toDateString());
        // created_at tetap waktu pencatatan, bukan tanggal bisnis.
        $this->assertSame(now()->toDateString(), $past->created_at->toDateString());
        $this->assertNotSame($past->order_date->toDateString(), $past->created_at->toDateString());
    }

    public function test_developer_sees_exact_order_date(): void
    {
        $dev = User::factory()->developer()->create();
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $dist->distributorProfile()->create(['status' => 'approved']);
        $p = Product::factory()->create(['status' => 'active', 'price' => 12000, 'distributor_price' => 10000]);
        Inventory::create(['product_id' => $p->id, 'stock' => 1000, 'reserved' => 0, 'threshold' => 10]);

        $po = app(PurchaseOrderService::class)->createOrder($dist, [['product_id' => $p->id, 'qty' => 2]], [
            'fulfillment' => 'pickup', 'order_date' => '2025-12-24',
        ]);

        $this->actingAs($dev)->get(route('developer.po.show', $po))
            ->assertOk()
            ->assertSee('24 Dec 2025', false);
    }
}
