<?php

namespace Database\Seeders;

use App\Models\Tank;
use App\Models\TankReading;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/** Two tanks with 35 days of hourly dummy ATG readings. Deterministic, so the demo looks the same every time. */
class TankSeeder extends Seeder
{
    /** name, capacity, starting fuel %, fuel drop per working day (% of capacity), random seed */
    private const TANKS = [
        ['Tangki 1', 2300, 78.0, 21.0, 1101],
        ['Tangki 2', 2800, 64.0, 17.0, 2202],
    ];

    public function run(): void
    {
        foreach (self::TANKS as [$name, $capacity, $startFuel, $dailyDrop, $seed]) {
            $tank = Tank::query()->updateOrCreate(['name' => $name], ['capacity_liters' => $capacity]);
            $this->fill($tank, $startFuel, $dailyDrop, $seed);
        }
    }

    /** Replace a tank's readings with a fresh series that ends at the current hour. */
    public static function refresh(?CarbonImmutable $until = null): void
    {
        foreach (self::TANKS as [$name, , $startFuel, $dailyDrop, $seed]) {
            $tank = Tank::query()->where('name', $name)->first();
            if ($tank) {
                (new self)->fill($tank, $startFuel, $dailyDrop, $seed, $until);
            }
        }
    }

    private function fill(Tank $tank, float $startFuel, float $dailyDrop, int $seed, ?CarbonImmutable $until = null): void
    {
        TankReading::query()->where('tank_id', $tank->id)->delete();

        foreach (array_chunk(self::series($tank->id, $startFuel, $dailyDrop, $seed, $until), 500) as $chunk) {
            TankReading::query()->insert($chunk);
        }
    }

    /**
     * Fuel falls during working hours and the tank is refilled when it runs low;
     * water stays a few percent. Total is always fuel plus water.
     *
     * @return list<array<string, mixed>>
     */
    public static function series(int $tankId, float $startFuel, float $dailyDrop, int $seed, ?CarbonImmutable $until = null, int $days = 35): array
    {
        mt_srand($seed);

        $until = CarbonImmutable::instance($until ?? now())->startOfHour();
        $fuel = $startFuel;
        $water = 2.0;
        $rows = [];

        for ($at = $until->subDays($days); $at <= $until; $at = $at->addHour()) {
            $hour = (int) $at->format('G');

            if ($hour >= 6 && $hour <= 17) {
                $fuel -= $dailyDrop / 12 * (0.6 + mt_rand(0, 80) / 100);
            }
            if ($fuel < 22) {
                $fuel = 88 + mt_rand(0, 60) / 10; // delivery
                $water += mt_rand(0, 40) / 100;
            }
            $water = min(6.0, max(0.8, $water + mt_rand(-5, 5) / 100));

            $fuelPct = round($fuel, 2);
            $waterPct = round($water, 2);
            if ($fuelPct + $waterPct > 100) {
                $fuelPct = round(100 - $waterPct, 2);
            }

            $rows[] = [
                'tank_id' => $tankId,
                'read_at' => $at->format('Y-m-d H:i:s'),
                'total_pct' => round($fuelPct + $waterPct, 2),
                'fuel_pct' => $fuelPct,
                'water_pct' => $waterPct,
            ];
        }

        return $rows;
    }
}
