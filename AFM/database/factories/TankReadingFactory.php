<?php

namespace Database\Factories;

use App\Models\Tank;
use App\Models\TankReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TankReading> */
class TankReadingFactory extends Factory
{
    public function definition(): array
    {
        $fuel = fake()->randomFloat(2, 20, 90);
        $water = fake()->randomFloat(2, 0, 5);

        return [
            'tank_id' => Tank::factory(),
            'read_at' => now()->startOfHour(),
            'total_pct' => round($fuel + $water, 2),
            'fuel_pct' => $fuel,
            'water_pct' => $water,
        ];
    }
}
