<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Authenticate::redirectUsing(fn () => '/login');

        Route::get('/login', fn () => 'login')->name('login');
        Route::middleware(['auth', 'role:developer'])->get('/_dev-only', fn () => 'dev-ok');
        Route::middleware(['auth', 'distributor.status:approved'])->get('/_dist-approved', fn () => 'dist-ok');
    }

    public function test_distributor_cannot_access_developer_route(): void
    {
        $user = User::factory()->create(['role' => 'distributor', 'status' => 'active']);

        $this->actingAs($user)->get('/_dev-only')->assertForbidden();
    }

    public function test_developer_can_access_developer_route(): void
    {
        $dev = User::factory()->developer()->create();

        $this->actingAs($dev)->get('/_dev-only')->assertOk();
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->get('/_dev-only')->assertRedirect('/login');
        $this->get('/_dist-approved')->assertRedirect('/login');
    }

    public function test_pending_distributor_is_redirected_from_approved_route(): void
    {
        $user = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $user->distributorProfile()->create(['status' => 'pending']);

        $this->actingAs($user)->get('/_dist-approved')->assertRedirect('/distributor/pending');
    }

    public function test_approved_distributor_can_access_approved_route(): void
    {
        $user = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $user->distributorProfile()->create(['status' => 'approved']);

        $this->actingAs($user)->get('/_dist-approved')->assertOk();
    }
}
