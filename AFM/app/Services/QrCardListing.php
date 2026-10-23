<?php

namespace App\Services;

use App\Enums\ResetPeriod;
use App\Enums\Validity;
use App\Models\QrCard;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The QR card table: filters, sorting and row presentation, shared by the page
 * and the Excel export so the export always matches what is on screen.
 */
class QrCardListing
{
    /** Query-string keys that filter and sort the card table; shared by the page and its export. */
    public const FILTER_KEYS = ['validity', 'state', 'reset', 'category', 'vehicle_type', 'search', 'sort', 'dir'];

    private const SORTS = [
        'plate' => 'qr_cards.plate_number',
        'category' => 'qr_cards.category',
        'vehicle_type' => 'qr_cards.vehicle_type_name',
        'reset' => 'qr_cards.reset_enabled',
        'quota' => 'qr_cards.quota',
    ];

    public function __construct(
        private readonly QuotaService $quota,
        private readonly PeriodService $periods,
        private readonly SettingsService $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $filters  validity, state, reset, category, vehicle_type, search, sort, dir
     * @return Builder<QrCard>
     */
    public function query(array $filters, ?CarbonInterface $now = null): Builder
    {
        $now ??= now();
        $query = $this->quota->query($now);

        if (in_array($filters['validity'] ?? null, array_column(Validity::cases(), 'value'), true)) {
            $query->where('qr_cards.validity', $filters['validity']);
        }

        if (in_array($filters['state'] ?? null, ['active', 'expired', 'depleted', 'low'], true)) {
            $this->quota->applyState($query, $filters['state'], $now);
        }

        if (($filters['reset'] ?? null) === 'on') {
            $query->where('qr_cards.reset_enabled', true);
        } elseif (($filters['reset'] ?? null) === 'off') {
            $query->where('qr_cards.reset_enabled', false);
        }

        if (in_array($filters['category'] ?? null, ['internal', 'external'], true)) {
            $query->where('qr_cards.category', $filters['category']);
        }

        if (! empty($filters['vehicle_type'])) {
            $query->where('qr_cards.vehicle_type_id', (int) $filters['vehicle_type']);
        }

        if (filled($filters['search'] ?? null)) {
            $term = CardService::normalizePlate((string) $filters['search']);
            $query->where('qr_cards.plate_number', 'like', '%'.addcslashes($term, '%_\\').'%');
        }

        $this->sort($query, (string) ($filters['sort'] ?? 'remaining'), strtolower((string) ($filters['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc', $now);

        return $query;
    }

    /** @return array<string, mixed> */
    public function present(QrCard $card, ?CarbonInterface $now = null): array
    {
        $now ??= now();
        $expired = $card->isExpired($now);
        $depleted = ! $expired && $card->remaining <= 0;
        $low = ! $expired && ! $depleted
            && $card->remaining <= $card->quota * $this->quota->lowQuotaPercent() / 100;

        return [
            'id' => $card->id,
            'token' => $card->token,
            'plate_number' => $card->plate_number,
            'category' => $card->category->value,
            'category_label' => $card->category->label(),
            'vehicle_type_id' => $card->vehicle_type_id,
            'vehicle_type' => $card->vehicle_type_name,
            'validity' => $card->validity->value,
            'validity_label' => $card->validity->label(),
            'valid_days' => $card->valid_days,
            'issued_at' => $card->issued_at->toIso8601String(),
            'expires_at' => $card->expires_at?->toIso8601String(),
            'reset_enabled' => $card->reset_enabled,
            'quota' => $card->quota,
            'max_capacity' => $card->max_capacity,
            'remaining' => $card->remaining,
            'expired' => $expired,
            'depleted' => $depleted,
            'low' => $low,
            'next_reset' => $depleted && $card->reset_enabled
                ? $this->periods->nextStart($this->settings->resetPeriod(), $now)->toIso8601String()
                : null,
        ];
    }

    /**
     * Text badges for exports and screen readers.
     *
     * @param  array<string, mixed>  $row  output of present()
     * @return list<string>
     */
    public function stateLabels(array $row): array
    {
        $labels = [];
        $labels[] = $row['expired'] ? 'Kedaluwarsa' : 'Aktif';
        if ($row['validity'] === Validity::Lifetime->value) {
            $labels[] = 'Lifetime';
        }
        if ($row['depleted']) {
            $labels[] = 'Habis';
        }
        if ($row['low']) {
            $labels[] = 'Hampir habis';
        }

        return $labels;
    }

    /** Header text of the reset column: "Reset per hari", or "Reset per minggu". */
    public function resetLabel(): string
    {
        return 'Reset per '.$this->settings->resetPeriod()->unit();
    }

    public function resetPeriod(): ResetPeriod
    {
        return $this->settings->resetPeriod();
    }

    /**
     * @param  Builder<QrCard>  $query
     */
    private function sort(Builder $query, string $key, string $dir, CarbonInterface $now): void
    {
        $direction = $dir === 'desc' ? 'desc' : 'asc';

        match ($key) {
            'remaining' => $query->orderBy(DB::raw(QuotaService::REMAINING_SQL), $direction),
            'expiry' => $query->orderByRaw('qr_cards.expires_at IS NULL')->orderBy('qr_cards.expires_at', $direction),
            'status' => $this->orderByStatus($query, $direction, $now),
            default => $query->orderBy(self::SORTS[$key] ?? self::SORTS['plate'], $direction),
        };

        if ($key !== 'plate') {
            $query->orderBy('qr_cards.plate_number');
        }
        $query->orderBy('qr_cards.id');
    }

    /**
     * Depleted first, then low quota, then fine, then expired.
     *
     * @param  Builder<QrCard>  $query
     * @param  'asc'|'desc'  $direction
     */
    private function orderByStatus(Builder $query, string $direction, CarbonInterface $now): void
    {
        $query->selectRaw(
            'CASE WHEN qr_cards.expires_at IS NOT NULL AND qr_cards.expires_at < ? THEN 3 '
            .'WHEN '.QuotaService::REMAINING_SQL.' <= 0 THEN 0 '
            .'WHEN '.QuotaService::REMAINING_SQL.' <= qr_cards.quota * ? / 100 THEN 1 ELSE 2 END AS status_rank',
            [$this->periods->toDb($now), $this->quota->lowQuotaPercent()],
        )->orderBy('status_rank', $direction);
    }
}
