<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Client> */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->optional()->company(),
            'nip' => null,
            'billing_address' => ['city' => 'Katowice', 'postal_code' => '40-007'],
            'marketing_consent' => false,
            'privacy_policy_version' => '2026-01',
            'privacy_policy_accepted_at' => now(),
        ];
    }
}
