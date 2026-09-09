<?php

namespace Database\Factories;

use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Option> */
class OptionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'code' => str($name)->slug('_'),
            'input_type' => 'select',
            'pricing_model' => 'fixed',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
