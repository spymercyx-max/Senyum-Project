<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_login_redirects_to_control_center(): void
    {
        User::factory()->developer()->create([
            'username' => 'developer',
            'password' => 'SenyumDev123!',
        ]);

        $response = $this->post('/login', [
            'login' => 'developer',
            'password' => 'SenyumDev123!',
        ]);

        $response->assertRedirect(route('developer.dashboard'));
        $this->assertAuthenticated();

        $this->get(route('developer.dashboard'))->assertOk();
    }

    public function test_approved_distributor_login_redirects_to_field_workspace(): void
    {
        $user = User::factory()->create([
            'username' => 'budi',
            'password' => 'Distributor123!',
            'role' => 'distributor',
            'status' => 'active',
        ]);
        $user->distributorProfile()->create(['status' => 'approved']);

        $response = $this->post('/login', [
            'login' => 'budi',
            'password' => 'Distributor123!',
        ]);

        $response->assertRedirect(route('distributor.dashboard'));
        $this->get(route('distributor.dashboard'))->assertOk();
    }

    public function test_pending_distributor_redirects_to_pending_screen(): void
    {
        $user = User::factory()->create([
            'username' => 'calon',
            'password' => 'Distributor123!',
            'role' => 'distributor',
            'status' => 'active',
        ]);
        $user->distributorProfile()->create(['status' => 'pending']);

        $this->post('/login', ['login' => 'calon', 'password' => 'Distributor123!'])
            ->assertRedirect(route('distributor.pending'));

        $this->get(route('distributor.dashboard'))->assertRedirect(route('distributor.pending'));
        $this->get(route('distributor.pending'))->assertOk();
    }

    public function test_rejected_and_suspended_redirects(): void
    {
        foreach (['rejected', 'suspended'] as $status) {
            $user = User::factory()->create([
                'username' => 'u_' . $status,
                'password' => 'Distributor123!',
                'role' => 'distributor',
                'status' => 'active',
            ]);
            $user->distributorProfile()->create(['status' => $status]);

            $this->post('/login', ['login' => 'u_' . $status, 'password' => 'Distributor123!'])
                ->assertRedirect(route('distributor.' . $status));

            auth()->logout();
        }
    }

    public function test_invalid_login_returns_error(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'Distributor123!']);

        $this->post('/login', ['login' => 'budi', 'password' => 'salah'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_with_email_also_works(): void
    {
        User::factory()->developer()->create([
            'username' => 'devmail',
            'email' => 'dev@mail.local',
            'password' => 'SenyumDev123!',
        ]);

        $this->post('/login', ['login' => 'dev@mail.local', 'password' => 'SenyumDev123!'])
            ->assertRedirect(route('developer.dashboard'));
    }

    public function test_guest_cannot_open_workspaces(): void
    {
        $this->get(route('developer.dashboard'))->assertRedirect(route('login'));
        $this->get(route('distributor.dashboard'))->assertRedirect(route('login'));
    }

    public function test_distributor_cannot_open_developer_area(): void
    {
        $user = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $user->distributorProfile()->create(['status' => 'approved']);

        $this->actingAs($user)->get(route('developer.dashboard'))->assertForbidden();
    }

    public function test_registration_http_flow_creates_pending_distributor(): void
    {
        $response = $this->post('/register', [
            'name' => 'Calon HTTP',
            'username' => 'calonhttp',
            'whatsapp' => '628990001122',
            'city' => 'Malang',
            'district' => 'Klojen',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('distributor.pending'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => 'calonhttp', 'role' => 'distributor']);
        $this->assertDatabaseHas('distributor_profiles', ['status' => 'pending']);
    }

    public function test_product_ordering_and_whatsapp_inquiry(): void
    {
        $product = Product::factory()->create([
            'name' => 'Senyum Test',
            'status' => 'active',
            'price' => 25000,
        ]);

        $response = $this->get(route('products.show', $product->slug));
        $response->assertOk();
        $response->assertSee('Pesan Melalui WhatsApp', false);
        $response->assertSee('wa.me', false);
        $response->assertSee('Senyum Test', false);
    }
}
