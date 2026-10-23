<?php

namespace Database\Seeders;

use App\Enums\CardCategory;
use App\Enums\Validity;
use App\Models\FuelTransaction;
use App\Models\QrCard;
use App\Models\QuotaAdjustment;
use App\Models\User;
use App\Models\VehicleType;
use App\Services\CardService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * About 40 cards in every state (lifetime, temporary, expired, depleted, low
 * quota, reset off) with up to 90 days of fueling and top-up history. The
 * random generator is seeded, so the demo looks the same every time.
 */
class QrCardSeeder extends Seeder
{
    private const TYPE = [
        'GM' => 'Daihatsu Gran Max',
        'HL' => 'Toyota Hilux',
        'L3' => 'Mitsubishi L300',
        'EF' => 'Isuzu Elf',
        'HD' => 'Hino Dutro',
        'FS' => 'Mitsubishi Fuso Dump Truck',
        'KP' => 'Komatsu PC200 Excavator',
        'CT' => 'Caterpillar 320D Excavator',
    ];

    /**
     * category (i internal, e external), type, validity (L lifetime, T temporary), valid days,
     * reset on (1/0), issued N days ago, state profile, top-ups [days ago, liters, note]
     *
     * Profiles for reset-on cards: normal, low, depleted, idle, expired (today's usage).
     * Profiles for reset-off cards (a pool): pool-depleted, pool-low, pool-mid.
     */
    private const CARDS = [
        ['i', 'GM', 'L', null, 1, 100, 'normal', []],
        ['i', 'GM', 'L', null, 1, 100, 'normal', []],
        ['i', 'GM', 'L', null, 1, 100, 'depleted', []],
        ['i', 'GM', 'T', 90, 1, 40, 'low', []],
        ['i', 'GM', 'T', 30, 1, 25, 'normal', []],
        ['i', 'HL', 'L', null, 1, 100, 'normal', []],
        ['i', 'HL', 'L', null, 1, 100, 'low', []],
        ['i', 'HL', 'L', null, 0, 70, 'pool-depleted', [[35, 60, 'Top-up proyek jalan']]],
        ['i', 'HL', 'T', 60, 1, 20, 'normal', []],
        ['i', 'L3', 'L', null, 1, 100, 'normal', []],
        ['i', 'L3', 'L', null, 1, 100, 'idle', []],
        ['i', 'L3', 'T', 14, 1, 12, 'normal', []],
        ['i', 'EF', 'L', null, 1, 100, 'normal', []],
        ['i', 'EF', 'L', null, 1, 100, 'depleted', []],
        ['i', 'EF', 'T', 45, 0, 30, 'pool-mid', [[15, 100, 'Tambahan kuota']]],
        ['i', 'HD', 'L', null, 1, 100, 'normal', []],
        ['i', 'HD', 'L', null, 1, 100, 'normal', []],
        ['i', 'HD', 'L', null, 1, 100, 'low', []],
        ['i', 'FS', 'L', null, 1, 100, 'normal', []],
        ['i', 'FS', 'L', null, 1, 100, 'normal', []],
        ['i', 'FS', 'T', 30, 1, 90, 'expired', []],
        ['i', 'KP', 'L', null, 1, 100, 'normal', [[0, 50, 'Pekerjaan tambahan hari ini'], [20, 40, 'Pekerjaan tambahan']]],
        ['i', 'KP', 'L', null, 1, 100, 'depleted', []],
        ['i', 'KP', 'T', 20, 1, 35, 'expired', []],
        ['i', 'CT', 'L', null, 1, 100, 'normal', []],
        ['i', 'CT', 'L', null, 0, 80, 'pool-low', [[50, 600, 'Alokasi proyek tambang']]],
        ['e', 'GM', 'T', 30, 1, 20, 'normal', []],
        ['e', 'GM', 'T', 7, 1, 20, 'expired', []],
        ['e', 'HL', 'T', 30, 1, 15, 'normal', []],
        ['e', 'HL', 'T', 60, 0, 25, 'pool-mid', [[20, 150, 'Sewa bulanan']]],
        ['e', 'L3', 'T', 30, 1, 10, 'depleted', []],
        ['e', 'L3', 'T', 14, 1, 45, 'expired', []],
        ['e', 'EF', 'T', 30, 1, 14, 'normal', []],
        ['e', 'EF', 'T', 30, 1, 10, 'low', []],
        ['e', 'HD', 'T', 90, 1, 30, 'normal', []],
        ['e', 'HD', 'T', 10, 1, 6, 'normal', []],
        ['e', 'FS', 'T', 60, 1, 40, 'normal', []],
        ['e', 'FS', 'T', 30, 0, 20, 'pool-depleted', [[18, 200, 'Sewa harian dump truck']]],
        ['e', 'KP', 'T', 90, 1, 30, 'normal', []],
        ['e', 'CT', 'L', null, 0, 60, 'pool-mid', [[40, 300, 'Kontrak alat berat']]],
    ];

    private const PREFIXES = ['KT', 'B', 'DA', 'KH', 'AB'];

    private const LETTERS = ['AB', 'CD', 'EF', 'GH', 'JK', 'LM', 'NP', 'QR', 'ST', 'UV'];

    private CarbonImmutable $now;

    private CarbonImmutable $today;

    /** @var list<array<string, mixed>> */
    private array $fuelRows = [];

    public function run(CardService $cards): void
    {
        mt_srand(20261022);

        $this->now = now();
        $this->today = $this->now->startOfDay();
        $admin = User::query()->where('email', 'admin@example.com')->first();

        foreach (self::CARDS as $index => [$cat, $typeKey, $validity, $days, $reset, $issuedAgo, $profile, $topUps]) {
            $type = VehicleType::query()->where('name', self::TYPE[$typeKey])->firstOrFail();
            $issued = $this->today->subDays($issuedAgo)->setTime(7, mt_rand(0, 59));
            $temporary = $validity === 'T';

            $card = QrCard::query()->create([
                'token' => Str::random(40),
                'category' => $cat === 'i' ? CardCategory::Internal : CardCategory::External,
                'vehicle_type_id' => $type->id,
                'plate_number' => $this->plate($index),
                'validity' => $temporary ? Validity::Temporary : Validity::Lifetime,
                'valid_days' => $days,
                'issued_at' => $issued,
                'expires_at' => $temporary ? $cards->expiryFor($issued, $days) : null,
                'reset_enabled' => (bool) $reset,
                'created_by' => $admin?->id,
            ]);

            $this->addTopUps($card, $topUps, $admin);

            if ($reset) {
                $this->resetCardHistory($card, $type->quota_liters, $profile, $card->category === CardCategory::Internal);
            } else {
                $this->poolHistory($card, $type->quota_liters, array_sum(array_column($topUps, 1)), $profile);
            }
        }

        foreach (array_chunk($this->fuelRows, 500) as $chunk) {
            FuelTransaction::query()->insert($chunk);
        }
    }

    private function plate(int $index): string
    {
        $prefix = self::PREFIXES[$index % count(self::PREFIXES)];
        $number = 1000 + (($index + 3) * 137) % 8900;

        return sprintf('%s %d %s', $prefix, $number, self::LETTERS[$index % count(self::LETTERS)]);
    }

    /** @param list<array{0: int, 1: int, 2: string}> $topUps */
    private function addTopUps(QrCard $card, array $topUps, ?User $admin): void
    {
        foreach ($topUps as [$daysAgo, $liters, $note]) {
            $at = $this->today->subDays($daysAgo)->setTime(8, 30);
            if ($at->greaterThan($this->now)) {
                $at = $this->now->subMinutes(10);
            }
            if ($at->lessThan($card->issued_at)) {
                $at = CarbonImmutable::instance($card->issued_at)->addHour();
            }

            QuotaAdjustment::query()->create([
                'qr_card_id' => $card->id,
                'liters' => $liters,
                'note' => $note,
                'user_id' => $admin?->id,
                'created_at' => $at,
            ]);
        }
    }

    /** Daily fueling for a card whose quota is refilled every period. */
    private function resetCardHistory(QrCard $card, float $quota, string $profile, bool $internal): void
    {
        $first = max(0, min(89, (int) $card->issued_at->startOfDay()->diffInDays($this->today)));
        $last = $card->expires_at ? CarbonImmutable::instance($card->expires_at)->startOfDay() : null;

        for ($daysAgo = $first; $daysAgo >= 1; $daysAgo--) {
            $date = $this->today->subDays($daysAgo);
            if ($last && $date->greaterThan($last)) {
                continue;
            }

            $weekend = $date->isWeekend();
            $chance = $internal ? ($weekend ? 0.35 : 0.85) : ($weekend ? 0.3 : 0.6);
            if (mt_rand(0, 99) / 100 > $chance) {
                continue;
            }

            $total = round($quota * (0.35 + mt_rand(0, 60) / 100), 2);
            $this->splitFueling($card, $date, $total);
        }

        $todayTotal = match ($profile) {
            'depleted' => $quota,
            'low' => round($quota * 0.88, 2),
            'normal' => round($quota * (0.2 + mt_rand(0, 40) / 100), 2),
            default => 0.0,
        };

        if ($todayTotal > 0) {
            $this->splitFueling($card, $this->today, $todayTotal, onlyUntilNow: true);
        }
    }

    /** Fueling spread over the life of a card whose quota is a single pool. */
    private function poolHistory(QrCard $card, float $quota, float $topUpTotal, string $profile): void
    {
        $pool = $quota + $topUpTotal;
        $remaining = match ($profile) {
            'pool-depleted' => 0.0,
            'pool-low' => round($quota * 0.15, 2),
            default => round($pool * 0.5, 2),
        };
        $used = round($pool - $remaining, 2);

        $span = max(1, min(89, (int) $card->issued_at->startOfDay()->diffInDays($this->today)));
        $events = mt_rand(8, 14);
        $weights = array_map(fn () => 0.5 + mt_rand(0, 100) / 100, range(1, $events));
        $sumWeights = array_sum($weights);

        $assigned = 0.0;
        for ($i = 0; $i < $events; $i++) {
            $liters = $i === $events - 1 ? round($used - $assigned, 2) : round($used * $weights[$i] / $sumWeights, 2);
            $assigned += $liters;

            $date = $this->today->subDays(mt_rand(0, $span));
            $this->fuelRows[] = $this->fuelRow($card, $this->momentOn($date, $date->equalTo($this->today)), $liters);
        }
    }

    private function splitFueling(QrCard $card, CarbonImmutable $date, float $total, bool $onlyUntilNow = false): void
    {
        $isToday = $onlyUntilNow;
        $first = $total >= 10 && mt_rand(0, 1) ? round($total * 0.55, 2) : $total;

        $this->fuelRows[] = $this->fuelRow($card, $this->momentOn($date, $isToday), $first);
        if ($first < $total) {
            $this->fuelRows[] = $this->fuelRow($card, $this->momentOn($date, $isToday), round($total - $first, 2));
        }
    }

    /** A random moment during working hours; today's moments never lie in the future. */
    private function momentOn(CarbonImmutable $date, bool $isToday): CarbonImmutable
    {
        if ($isToday) {
            $seconds = max(1, (int) $this->today->diffInSeconds($this->now) - 1);

            return $this->today->addSeconds(mt_rand(0, $seconds));
        }

        return $date->setTime(mt_rand(5, 17), mt_rand(0, 59), mt_rand(0, 59));
    }

    /** @return array<string, mixed> */
    private function fuelRow(QrCard $card, CarbonImmutable $at, float $liters): array
    {
        return [
            'qr_card_id' => $card->id,
            'liters' => $liters,
            'dispensed_at' => $at->format('Y-m-d H:i:s'),
            'source' => 'dummy',
            'dispenser_id' => null,
            'created_at' => $at->format('Y-m-d H:i:s'),
        ];
    }
}
