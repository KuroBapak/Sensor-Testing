<?php

namespace App\Services;

use App\Models\Tank;
use App\Models\TankReading;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/** Tank levels as the Main Tank view needs them. Phase 2 swaps the data source only. */
class TankService
{
    /**
     * @return array{
     *     id: int, name: string, capacity: float,
     *     latest: array<string, mixed>|null,
     *     chart: list<array<string, mixed>>,
     *     recent: list<array<string, mixed>>,
     * }
     */
    public function panel(Tank $tank, ?CarbonInterface $now = null): array
    {
        $now ??= now();
        $capacity = (float) $tank->capacity_liters;

        $latest = $tank->readings()->where('read_at', '<=', $now)->orderByDesc('read_at')->first();

        $chart = $tank->readings()
            ->where('read_at', '>', $now->subHours(24))
            ->where('read_at', '<=', $now)
            ->orderBy('read_at')
            ->get();

        $recent = $tank->readings()
            ->where('read_at', '>', $now->subHours(5))
            ->where('read_at', '<=', $now)
            ->orderByDesc('read_at')
            ->get();

        return [
            'id' => $tank->id,
            'name' => $tank->name,
            'capacity' => $capacity,
            'latest' => $latest ? $this->present($latest, $capacity) : null,
            'chart' => $this->presentMany($chart, $capacity),
            'recent' => $this->presentMany($recent, $capacity),
        ];
    }

    /**
     * @param  Collection<int, TankReading>  $readings
     * @return list<array<string, mixed>>
     */
    private function presentMany(Collection $readings, float $capacity): array
    {
        return array_values($readings->map(fn (TankReading $r) => $this->present($r, $capacity))->all());
    }

    /** @return array<string, mixed> */
    public function present(TankReading $reading, float $capacity): array
    {
        return [
            'read_at' => $reading->read_at->toIso8601String(),
            'total_pct' => $reading->total_pct,
            'fuel_pct' => $reading->fuel_pct,
            'water_pct' => $reading->water_pct,
            'empty_pct' => round(100 - $reading->total_pct, 2),
            'total_liters' => round($capacity * $reading->total_pct / 100, 1),
            'fuel_liters' => round($capacity * $reading->fuel_pct / 100, 1),
            'water_liters' => round($capacity * $reading->water_pct / 100, 1),
        ];
    }
}
