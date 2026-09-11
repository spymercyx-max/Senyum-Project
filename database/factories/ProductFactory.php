<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->randomNumber(5),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(15000, 50000),
            'sku' => 'SNY-' . strtoupper(fake()->unique()->bothify('???-###')),
            'image' => null,
            'status' => 'active',
            'featured' => false,
            'sort_order' => fake()->numberBetween(0, 100),
            'ingredients' => fake()->sentence(),
            'availability_note' => null,
        ];
    }
}
