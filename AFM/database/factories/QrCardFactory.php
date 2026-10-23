<?php

namespace Database\Factories;

use App\Enums\CardCategory;
use App\Enums\Validity;
use App\Models\QrCard;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<QrCard> */
class QrCardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'token' => Str::random(40),
            'category' => CardCategory::Internal,
            'vehicle_type_id' => VehicleType::factory(),
            'plate_number' => strtoupper(fake()->unique()->bothify('B #### ???')),
            'validity' => Validity::Lifetime,
            'valid_days' => null,
            'issued_at' => now()->subDays(30),
            'expires_at' => null,
            'reset_enabled' => true,
        ];
    }

    public function external(): static
    {
        return $this->state(fn () => ['category' => CardCategory::External]);
    }

    public function resetOff(): static
    {
        return $this->state(fn () => ['reset_enabled' => false]);
    }

    /** A Temporary card issued $issuedDaysAgo days ago that is valid for $days days. */
    public function temporary(int $days = 30, int $issuedDaysAgo = 0): static
    {
        return $this->state(function () use ($days, $issuedDaysAgo) {
            $issued = now()->subDays($issuedDaysAgo);

            return [
                'validity' => Validity::Temporary,
                'valid_days' => $days,
                'issued_at' => $issued,
                'expires_at' => $issued->setTimezone('Asia/Jakarta')->addDays($days - 1)->setTime(23, 59, 59),
            ];
        });
    }

    public function expired(): static
    {
        return $this->temporary(30, 60);
    }
}
