<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Territory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DistributorStockService;
use App\Services\PurchaseOrderService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TransactionCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private function setupFlow(): array
    {
        $dev = User::factory()->developer()->create();
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $dist->distributorProfile()->create(['status' => 'approved']);

        $territory = Territory::factory()->create();
        $outlet = Outlet::factory()->create([
            'territory_id' => $territory->id, 'distributor_id' => $dist->id, 'status' => 'active',
        ]);

        $a = Product::factory()->create(['status' => 'active', 'price' => 12000, 'distributor_price' => 10000]);
        Inventory::create(['product_id' => $a->id, 'stock' => 1000, 'reserved' => 0, 'threshold' => 10]);

        $po = app(PurchaseOrderService::class)->createOrder($dist, [
            ['product_id' => $a->id, 'qty' => 100],
        ], ['fulfillment' => 'pickup']);
        app(PurchaseOrderService::class)->transition($po, 'disetujui', $dev);

        $trx = app(TransactionService::class)->createTransaction($dist, $outlet, [
            ['product_id' => $a->id, 'qty' => 10, 'price' => 12000],
        ], ['type' => 'outlet']);

        return [$dist, $outlet, $a, $trx];
    }

    private function stockOf(User $dist, Product $p): int
    {
        return app(DistributorStockService::class)->available($dist, $p);
    }

    public function test_edit_quantity_down_returns_stock(): void
    {
        [$dist, , $a, $trx] = $this->setupFlow();
        $this->assertSame(90, $this->stockOf($dist, $a));

        $updated = app(TransactionService::class)->updateTransaction(
            $dist, $trx, [['product_id' => $a->id, 'qty' => 7, 'price' => 12000]], ['reason' => 'Salah catat.']
        );

        $this->assertSame(7 * 12000, $updated->total_amount);
        $this->assertSame(93, $this->stockOf($dist, $a)); // 3 kembali
    }

    public function test_edit_quantity_up_deducts_more(): void
    {
        [$dist, , $a, $trx] = $this->setupFlow();

        $updated = app(TransactionService::class)->updateTransaction(
            $dist, $trx, [['product_id' => $a->id, 'qty' => 15, 'price' => 12000]], []
        );

        $this->assertSame(15 * 12000, $updated->total_amount);
        $this->assertSame(85, $this->stockOf($dist, $a)); // 5 tambahan
    }

    public function test_edit_price_changes_total_without_stock_move(): void
    {
        [$dist, , $a, $trx] = $this->setupFlow();

        $updated = app(TransactionService::class)->updateTransaction(
            $dist, $trx, [['product_id' => $a->id, 'qty' => 10, 'price' => 13000]], []
        );

        $this->assertSame(10 * 13000, $updated->total_amount);
        $this->assertSame(90, $this->stockOf($dist, $a));
    }

    public function test_edit_blocked_when_would_go_negative(): void
    {
        [$dist, , $a, $trx] = $this->setupFlow();

        try {
            app(TransactionService::class)->updateTransaction(
                $dist, $trx, [['product_id' => $a->id, 'qty' => 1000, 'price' => 12000]], []
            );
            $this->fail('Harus ditolak agar stok tidak negatif.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }
        $this->assertSame(90, $this->stockOf($dist, $a));
        $this->assertSame(10 * 12000, $trx->refresh()->total_amount);
    }

    public function test_void_restores_exact_quantity_and_keeps_record(): void
    {
        [$dist, , $a, $trx] = $this->setupFlow();
        $id = $trx->id;

        app(TransactionService::class)->voidTransaction($dist, $trx, 'Input ganda.');

        $this->assertSame(100, $this->stockOf($dist, $a)); // 10 kembali utuh
        $this->assertSame(0, Transaction::count()); // tersembunyi dari list aktif
        $kept = Transaction::withTrashed()->findOrFail($id);
        $this->assertSame('void', $kept->status);
        $this->assertSame(10 * 12000, (int) $kept->total_amount); // data asli utuh

        try {
            app(TransactionService::class)->voidTransaction($dist, $kept);
            $this->fail('Void ganda harus ditolak.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }
        $this->assertSame(100, $this->stockOf($dist, $a));
    }

    public function test_detail_and_edit_pages_render(): void
    {
        [$dist, , , $trx] = $this->setupFlow();

        $this->actingAs($dist)->get(route('distributor.transaksi.show', $trx))
            ->assertOk()
            ->assertSee($trx->code, false)
            ->assertSee('Harga beli', false);

        $this->actingAs($dist)->get(route('distributor.transaksi.edit', $trx))
            ->assertOk()
            ->assertSee('SIMPAN KOREKSI', false);
    }

    public function test_correction_and_void_are_logged_immutably(): void
    {
        [$dist, , $a, $trx] = $this->setupFlow();

        app(TransactionService::class)->updateTransaction(
            $dist, $trx, [['product_id' => $a->id, 'qty' => 7, 'price' => 12000]], ['reason' => 'Koreksi.']
        );
        app(TransactionService::class)->voidTransaction($dist, $trx->refresh(), 'Batal total.');

        $logs = ActivityLog::where('entity', Transaction::class)->where('entity_id', $trx->id)->get();
        $actions = $logs->pluck('action')->all();

        $this->assertContains('transaction.created', $actions);
        $this->assertContains('transaction.corrected', $actions);
        $this->assertContains('transaction.voided', $actions);

        $corrected = $logs->firstWhere('action', 'transaction.corrected');
        $this->assertSame(10 * 12000, $corrected->metadata['old_total']);
        $this->assertSame(7 * 12000, $corrected->metadata['new_total']);
    }
}
