<?php

namespace Database\Factories;

use App\Models\Tank;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tank> */
class TankFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Tangki '.fake()->unique()->numberBetween(1, 99),
            'capacity_liters' => 2300,
        ];
    }
}
