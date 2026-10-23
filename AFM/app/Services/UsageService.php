<?php

namespace App\Services;

use App\Enums\CardCategory;
use App\Enums\ResetPeriod;
use App\Models\FuelTransaction;
use App\Models\QrCard;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/** Fuel consumption per card, by category and period. Totals are summed in the database. */
class UsageService
{
    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    private const BUCKETS = [
        'day' => 14,
        'week' => 12,
        'month' => 6,
    ];

    public function __construct(
        private readonly PeriodService $periods,
        private readonly QuotaService $quota,
    ) {}

    /**
     * Start and end (exclusive) of the day, week or month that contains $at.
     * Uses the same boundaries as the reset rules but never changes them.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function range(ResetPeriod $period, ?CarbonInterface $at = null): array
    {
        $start = $this->periods->startOf($period, $at);

        return [$start, $this->periods->shift($period, $start, 1)];
    }

    /**
     * Every card of a category with the liters used in [$from, $to).
     * Deleted cards stay in the result only if they have usage in the period.
     *
     * @return Collection<int, QrCard>
     */
    public function cardRows(CardCategory $category, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->quota->query()
            ->withTrashed()
            ->where('qr_cards.category', $category->value)
            ->selectRaw(
                '(SELECT COALESCE(SUM(liters), 0) FROM fuel_transactions '
                .'WHERE fuel_transactions.qr_card_id = qr_cards.id '
                .'AND dispensed_at >= ? AND dispensed_at < ?) as used_in_period',
                [$this->periods->toDb($from), $this->periods->toDb($to)],
            )
            ->orderBy('qr_cards.vehicle_type_name')
            ->orderBy('qr_cards.plate_number')
            ->orderBy('qr_cards.id')
            ->get()
            ->filter(fn (QrCard $card) => $card->deleted_at === null || $card->used_in_period > 0)
            ->values();
    }

    /**
     * The usage table: cards grouped by vehicle type, each group with its subtotal.
     *
     * @return list<array{vehicle_type: string, subtotal: float, rows: list<array<string, mixed>>}>
     */
    public function groupedRows(CardCategory $category, CarbonInterface $from, CarbonInterface $to): array
    {
        $groups = [];

        foreach ($this->cardRows($category, $from, $to) as $card) {
            $name = $card->vehicle_type_name;
            $groups[$name] ??= ['vehicle_type' => $name, 'subtotal' => 0.0, 'rows' => []];

            $groups[$name]['subtotal'] += $card->used_in_period;
            $groups[$name]['rows'][] = [
                'id' => $card->id,
                'plate_number' => $card->plate_number,
                'vehicle_type' => $name,
                'used' => $card->used_in_period,
                'quota' => $card->quota,
                'remaining' => $card->trashed() ? null : $card->remaining,
                'reset_enabled' => $card->reset_enabled,
                'deleted' => $card->trashed(),
            ];
        }

        return array_values(array_map(
            fn (array $group) => [...$group, 'subtotal' => round($group['subtotal'], 2)],
            $groups,
        ));
    }

    /** Total liters of a category in [$from, $to), deleted cards included. */
    public function total(CardCategory $category, CarbonInterface $from, CarbonInterface $to): float
    {
        return (float) FuelTransaction::query()
            ->join('qr_cards', 'qr_cards.id', '=', 'fuel_transactions.qr_card_id')
            ->where('qr_cards.category', $category->value)
            ->where('fuel_transactions.dispensed_at', '>=', $this->periods->toDb($from))
            ->where('fuel_transactions.dispensed_at', '<', $this->periods->toDb($to))
            ->sum('fuel_transactions.liters');
    }

    /**
     * Liters used per day, week or month for one card, oldest bucket first.
     *
     * @return list<array{start: string, label: string, liters: float}>
     */
    public function series(QrCard $card, ResetPeriod $granularity, ?CarbonInterface $now = null): array
    {
        $starts = $this->bucketStarts($granularity, $now);

        $buckets = [];
        foreach ($starts as $start) {
            $buckets[$start->toIso8601String()] = 0.0;
        }

        $card->fuelTransactions()
            ->where('dispensed_at', '>=', $this->periods->toDb($starts[0]))
            ->get(['liters', 'dispensed_at'])
            ->each(function (FuelTransaction $tx) use (&$buckets, $granularity) {
                $key = $this->periods->startOf($granularity, $tx->dispensed_at)->toIso8601String();
                if (array_key_exists($key, $buckets)) {
                    $buckets[$key] += $tx->liters;
                }
            });

        $points = [];
        foreach ($starts as $start) {
            $key = $start->toIso8601String();
            $points[] = [
                'start' => $key,
                'label' => $this->label($granularity, $start),
                'liters' => round($buckets[$key], 2),
            ];
        }

        return $points;
    }

    /**
     * Total liters of a whole category per day, week or month, oldest bucket
     * first. The last bucket is the current period, so it equals the total on the
     * Usage page. Each bucket is summed in the database.
     *
     * @return list<array{start: string, label: string, liters: float}>
     */
    public function totalSeries(CardCategory $category, ResetPeriod $granularity, ?CarbonInterface $now = null): array
    {
        $points = [];

        foreach ($this->bucketStarts($granularity, $now) as $start) {
            $end = $this->periods->shift($granularity, $start, 1);

            $points[] = [
                'start' => $start->toIso8601String(),
                'label' => $this->label($granularity, $start),
                'liters' => round($this->total($category, $start, $end), 2),
            ];
        }

        return $points;
    }

    /** @return Collection<int, FuelTransaction> */
    public function recentFuelings(QrCard $card, int $limit = 12): Collection
    {
        return $card->fuelTransactions()->orderByDesc('dispensed_at')->limit($limit)->get();
    }

    /**
     * Start of each bucket shown in a graph, oldest first, ending with the current period.
     *
     * @return list<CarbonImmutable>
     */
    private function bucketStarts(ResetPeriod $granularity, ?CarbonInterface $now): array
    {
        $current = $this->periods->startOf($granularity, $now);

        $starts = [];
        for ($i = self::BUCKETS[$granularity->value] - 1; $i >= 0; $i--) {
            $starts[] = $this->periods->shift($granularity, $current, -$i);
        }

        return $starts;
    }

    private function label(ResetPeriod $granularity, CarbonImmutable $start): string
    {
        return match ($granularity) {
            ResetPeriod::Day, ResetPeriod::Week => $start->format('d/m'),
            ResetPeriod::Month => self::MONTHS[$start->month - 1].' '.$start->year,
        };
    }
}
