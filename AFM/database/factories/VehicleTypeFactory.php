<?php

namespace Database\Factories;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VehicleType> */
class VehicleTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'max_capacity_liters' => 100,
            'quota_liters' => 20,
        ];
    }

    public function quota(float $liters): static
    {
        return $this->state(fn () => ['quota_liters' => $liters]);
    }
}
