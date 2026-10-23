<?php

namespace Database\Factories;

use App\Models\QrCard;
use App\Models\QuotaAdjustment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<QuotaAdjustment> */
class QuotaAdjustmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'qr_card_id' => QrCard::factory(),
            'liters' => fake()->randomFloat(2, 1, 20),
            'note' => null,
            'user_id' => null,
            'created_at' => now(),
        ];
    }
}
