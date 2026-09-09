<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'CC-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'status' => 'pending',
            'payment_status' => 'pending',
            'currency' => 'PLN',
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_company' => null,
            'billing_address' => ['city' => 'Katowice'],
            'shipping_method' => 'pickup',
            'subtotal' => 100,
            'shipping_total' => 0,
            'tax_rate' => 23,
            'invoice_required' => false,
            'total' => 100,
        ];
    }
}
