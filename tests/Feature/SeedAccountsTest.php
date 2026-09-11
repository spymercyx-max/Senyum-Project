<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_seed_state(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['X-Mercy', 'Madcapone', 'Ahmadalkaff'] as $username) {
            $u = User::where('username', $username)->first();
            $this->assertNotNull($u, "Developer {$username} harus ada.");
            $this->assertSame('developer', $u->role);
            $this->assertSame('active', $u->status);
        }

        // Konflik X-Mercy terselesaikan deterministik: distributor memakai
        // username unik X-Mercy-Dist, nama tampilan tetap X-Mercy.
        $dist = User::where('username', 'X-Mercy-Dist')->first();
        $this->assertNotNull($dist);
        $this->assertSame('distributor', $dist->role);
        $this->assertSame('X-Mercy', $dist->name);
        $this->assertSame('approved', $dist->distributorStatus());

        $this->assertSame(1, User::where('username', 'X-Mercy')->count());

        // Clean start: tanpa data demo.
        $this->assertSame(0, \App\Models\Product::count());
        $this->assertSame(0, \App\Models\Outlet::count());
        $this->assertSame(0, \App\Models\Transaction::count());
        $this->assertSame(0, \App\Models\PurchaseOrder::count());
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, User::where('role', 'developer')->count());
        $this->assertSame(1, User::where('role', 'distributor')->count());
    }

    public function test_new_accounts_can_login(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/login', ['login' => 'Madcapone', 'password' => 'fullsenyum'])
            ->assertRedirect(route('developer.dashboard'));
        auth()->logout();

        $this->post('/login', ['login' => 'X-Mercy-Dist', 'password' => 'fullsenyum'])
            ->assertRedirect(route('distributor.dashboard'));
    }
}
