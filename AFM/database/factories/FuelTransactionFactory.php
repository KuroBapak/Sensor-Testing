<?php

namespace Database\Factories;

use App\Models\FuelTransaction;
use App\Models\QrCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FuelTransaction> */
class FuelTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'qr_card_id' => QrCard::factory(),
            'liters' => fake()->randomFloat(2, 5, 20),
            'dispensed_at' => now(),
            'source' => 'dummy',
        ];
    }
}
