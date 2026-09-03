<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['druk', 'reklama', 'foto', 'uslugi']),
            'description' => fake()->sentence(),
            'image_path' => null,
            'calculator_type' => 'fixed',
            'starting_price' => fake()->randomFloat(2, 1, 250),
            'configuration' => ['starting_price' => 1],
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
