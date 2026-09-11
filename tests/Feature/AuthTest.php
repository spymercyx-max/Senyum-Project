<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_can_authenticate_with_username(): void
    {
        $developer = User::factory()->developer()->create([
            'username' => 'developer',
            'password' => 'SenyumDev123!',
        ]);

        $ok = Auth::attempt(['username' => 'developer', 'password' => 'SenyumDev123!']);

        $this->assertTrue($ok);
        $this->assertTrue(Auth::user()->isDeveloper());
    }

    public function test_distributor_can_authenticate(): void
    {
        User::factory()->create([
            'username' => 'budi',
            'password' => 'Distributor123!',
            'role' => 'distributor',
            'status' => 'active',
        ]);

        $this->assertTrue(Auth::attempt(['username' => 'budi', 'password' => 'Distributor123!']));
    }

    public function test_invalid_login_fails(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'Distributor123!']);

        $this->assertFalse(Auth::attempt(['username' => 'budi', 'password' => 'salah-password']));
        $this->assertGuest();
    }

    public function test_pending_distributor_is_not_approved(): void
    {
        $user = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $user->distributorProfile()->create(['status' => 'pending']);

        $this->assertFalse($user->isApprovedDistributor());
        $this->assertSame('pending', $user->distributorStatus());
    }
}
