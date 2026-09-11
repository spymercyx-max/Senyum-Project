<?php

namespace Database\Factories;

use App\Services\TerritoryService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Territory>
 */
class TerritoryFactory extends Factory
{
    public function definition(): array
    {
        $city = fake()->city();
        $district = fake()->streetName();

        return [
            'city' => $city,
            'district' => $district,
            'city_normalized' => TerritoryService::normalizeCity($city),
            'district_normalized' => TerritoryService::normalizeDistrict($district . '-' . fake()->unique()->randomNumber(5)),
            'code' => null,
            'status' => 'active',
            'notes' => null,
        ];
    }
}
