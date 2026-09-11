<?php

namespace Tests\Feature;

use App\Models\Territory;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_profile_and_territory(): void
    {
        $service = app(RegistrationService::class);

        $user = $service->register([
            'name' => 'Calon Distributor',
            'username' => 'calonbaru',
            'password' => 'password123',
            'whatsapp' => '6289990001111',
            'city' => 'Malang',
            'district' => 'Klojen',
        ]);

        $this->assertDatabaseHas('users', ['username' => 'calonbaru', 'role' => 'distributor']);
        $this->assertDatabaseHas('profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('distributor_profiles', ['user_id' => $user->id, 'status' => 'pending']);
        $this->assertDatabaseHas('territories', ['city_normalized' => 'malang', 'district_normalized' => 'klojen']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'distributor.registered']);
    }

    public function test_duplicate_city_case_insensitive_reuses_same_territory(): void
    {
        $service = app(RegistrationService::class);

        $first = $service->register([
            'name' => 'Satu',
            'username' => 'usersatu',
            'password' => 'password123',
            'whatsapp' => '6281110000001',
            'city' => '  MALANG ',
            'district' => 'Klojen',
        ]);

        $second = $service->register([
            'name' => 'Dua',
            'username' => 'userdua',
            'password' => 'password123',
            'whatsapp' => '6281110000002',
            'city' => 'malang',
            'district' => '  KLOJEN  ',
        ]);

        $this->assertSame(
            $first->distributorProfile->territory_id,
            $second->distributorProfile->territory_id
        );
        $this->assertSame(1, Territory::count());
    }
}
