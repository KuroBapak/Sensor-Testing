<?php

namespace App\Services;

use App\Models\QrCard;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * The one place that decides how many liters a card has left (section 3).
 *
 * Remaining liters are calculated, never stored:
 *   reset on  = quota + top-ups this period - fuel taken this period
 *   reset off = quota + all top-ups          - all fuel taken
 * where quota is always the vehicle type's current quota. The web screens use
 * this now; the phase 2 device API calls the same code so a card is judged the
 * same way on screen and at the dispenser.
 */
class QuotaService
{
    /** Expression over the derived table built by query(). */
    public const REMAINING_SQL = 'ROUND(CASE WHEN qr_cards.reset_enabled THEN '
        .'qr_cards.quota + qr_cards.adj_period - qr_cards.used_period '
        .'ELSE qr_cards.quota + qr_cards.adj_total - qr_cards.used_total END, 2)';

    public function __construct(
        private readonly PeriodService $periods,
        private readonly SettingsService $settings,
    ) {}

    /**
     * QR cards with their quota, the four fuel/top-up sums and `remaining` as
     * columns, so lists can filter and sort on remaining liters in the database.
     * The result is a normal Eloquent builder (soft-deleted cards are hidden
     * unless ->withTrashed() is called).
     *
     * @return Builder<QrCard>
     */
    public function query(?CarbonInterface $now = null): Builder
    {
        $periodStart = $this->periods->toDb($this->periods->currentStart($now));

        $inner = DB::table('qr_cards')
            ->join('vehicle_types as vt', 'vt.id', '=', 'qr_cards.vehicle_type_id')
            ->select('qr_cards.*')
            ->addSelect([
                'vt.name as vehicle_type_name',
                'vt.quota_liters as quota',
                'vt.max_capacity_liters as max_capacity',
            ])
            ->selectSub(fn (QueryBuilder $q) => $this->sum($q, 'fuel_transactions', 'dispensed_at', $periodStart), 'used_period')
            ->selectSub(fn (QueryBuilder $q) => $this->sum($q, 'quota_adjustments', 'created_at', $periodStart), 'adj_period')
            ->selectSub(fn (QueryBuilder $q) => $this->sum($q, 'fuel_transactions'), 'used_total')
            ->selectSub(fn (QueryBuilder $q) => $this->sum($q, 'quota_adjustments'), 'adj_total');

        return QrCard::query()
            ->fromSub($inner, 'qr_cards')
            ->select('qr_cards.*')
            ->selectRaw(self::REMAINING_SQL.' as remaining');
    }

    /** Remaining liters of one card right now. */
    public function remaining(QrCard $card, ?CarbonInterface $now = null): float
    {
        return (float) ($this->query($now)->withTrashed()->whereKey($card->getKey())->value('remaining') ?? 0.0);
    }

    /**
     * Narrow a query() builder to a card state: active, lifetime, expired,
     * depleted or low.
     *
     * @param  Builder<QrCard>  $query
     */
    public function applyState(Builder $query, string $state, ?CarbonInterface $now = null): void
    {
        $now = $this->periods->toDb($now ?? now());

        match ($state) {
            'active' => $this->notExpired($query, $now),
            'lifetime' => $query->where('qr_cards.validity', 'lifetime'),
            'expired' => $query->whereNotNull('qr_cards.expires_at')->where('qr_cards.expires_at', '<', $now),
            'depleted' => $this->notExpired($query, $now)->whereRaw(self::REMAINING_SQL.' <= 0'),
            'low' => $this->notExpired($query, $now)
                ->whereRaw(self::REMAINING_SQL.' > 0')
                ->whereRaw(self::REMAINING_SQL.' <= qr_cards.quota * ? / 100', [$this->lowQuotaPercent()]),
            default => null,
        };
    }

    /**
     * Counter cards on the QR page.
     *
     * @return array{active: int, lifetime: int, expired: int, depleted: int}
     */
    public function counters(?CarbonInterface $now = null): array
    {
        $counts = [];
        foreach (['active', 'lifetime', 'expired', 'depleted'] as $state) {
            $query = $this->query($now);
            $this->applyState($query, $state, $now);
            $counts[$state] = $query->count();
        }

        return $counts;
    }

    public function lowQuotaPercent(): int
    {
        return (int) $this->settings->get('low_quota_percent');
    }

    /**
     * @param  Builder<QrCard>  $query
     * @return Builder<QrCard>
     */
    private function notExpired(Builder $query, string $now): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereNull('qr_cards.expires_at')
            ->orWhere('qr_cards.expires_at', '>=', $now));
    }

    private function sum(QueryBuilder $query, string $table, ?string $timeColumn = null, ?string $since = null): QueryBuilder
    {
        $query->from($table)
            ->selectRaw('COALESCE(SUM(liters), 0)')
            ->whereColumn("$table.qr_card_id", 'qr_cards.id');

        if ($timeColumn !== null && $since !== null) {
            $query->where("$table.$timeColumn", '>=', $since);
        }

        return $query;
    }
}
