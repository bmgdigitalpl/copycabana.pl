<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'product_name' => fake()->words(2, true),
            'product_slug' => fake()->slug(),
            'configuration' => [],
            'unit_price' => 10,
            'quantity' => 1,
            'total' => 10,
        ];
    }
}
