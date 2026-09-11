<?php

namespace Database\Factories;

use App\Models\Territory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Outlet>
 */
class OutletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'territory_id' => Territory::factory(),
            'distributor_id' => User::factory(),
            'name' => 'Toko ' . fake()->company(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'district' => fake()->streetName(),
            'phone' => fake()->optional()->phoneNumber(),
            'latitude' => null,
            'longitude' => null,
            'status' => 'active',
            'notes' => null,
        ];
    }
}
