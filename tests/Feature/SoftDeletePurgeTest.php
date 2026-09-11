<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\Territory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeletePurgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_delete_hides_but_retains(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $this->assertTrue($product->trashed() === false);
        $product->delete();

        $this->assertSame(0, Product::count());
        $this->assertSame(1, Product::withTrashed()->count());
        // Katalog publik tidak menampilkan yang dihapus.
        $this->assertSame(0, Product::active()->count());
    }

    public function test_purge_command_respects_retention(): void
    {
        $fresh = Product::factory()->create(['name' => 'Baru Dihapus']);
        $fresh->delete(); // deleted_at = now

        $old = Product::factory()->create(['name' => 'Lama Dihapus']);
        $old->delete();
        $old->deleted_at = now()->subDays(61);
        $old->save();

        // Dry run: tidak menghapus.
        $this->artisan('senyum:purge-expired-deleted-data', ['--dry-run' => true])->assertOk();
        $this->assertSame(2, Product::withTrashed()->count());

        $this->artisan('senyum:purge-expired-deleted-data')->assertOk();
        $this->assertNull(Product::withTrashed()->find($old->id));
        $this->assertNotNull(Product::withTrashed()->find($fresh->id));

        // Retensi dapat dikonfigurasi.
        $this->artisan('senyum:purge-expired-deleted-data', ['--days' => 0])->assertOk();
        $this->assertNull(Product::withTrashed()->find($fresh->id));
    }

    public function test_outlet_and_transaction_soft_delete(): void
    {
        $territory = Territory::factory()->create();
        $dist = User::factory()->create(['role' => 'distributor']);
        $outlet = Outlet::factory()->create(['territory_id' => $territory->id, 'distributor_id' => $dist->id]);
        $outlet->delete();

        $this->assertSame(0, Outlet::count());
        $this->assertSame(1, Outlet::withTrashed()->count());

        $trx = Transaction::create([
            'code' => 'TRX-TEST-1', 'distributor_id' => $dist->id, 'outlet_id' => $outlet->id,
            'status' => 'completed', 'type' => 'outlet', 'total_amount' => 1000, 'sold_at' => now(),
        ]);
        $trx->delete();
        $this->assertSame(0, Transaction::count());
        $this->assertSame(1, Transaction::withTrashed()->count());
    }
}
