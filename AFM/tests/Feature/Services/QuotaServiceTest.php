<?php

use App\Models\FuelTransaction;
use App\Models\QrCard;
use App\Models\QuotaAdjustment;
use App\Models\VehicleType;
use App\Services\QuotaService;

beforeEach(function () {
    // Thursday 22 Oct 2026, 14:30 WIB.
    $this->travelTo('2026-10-22 14:30:00');
});

function fuel(QrCard $card, float $liters, string $at): void
{
    FuelTransaction::factory()->for($card)->create(['liters' => $liters, 'dispensed_at' => $at]);
}

function topUp(QrCard $card, float $liters, string $at): void
{
    QuotaAdjustment::factory()->for($card)->create(['liters' => $liters, 'created_at' => $at]);
}

function remaining(QrCard $card): float
{
    return app(QuotaService::class)->remaining($card);
}

test('reset on: remaining is quota plus top-ups this period minus fuel this period', function () {
    $card = QrCard::factory()->for(VehicleType::factory()->quota(20))->create();

    fuel($card, 5, '2026-10-21 10:00:00');   // yesterday: a new day started, so it no longer counts
    fuel($card, 7, '2026-10-22 08:00:00');
    topUp($card, 3, '2026-10-21 09:00:00');  // yesterday: ignored
    topUp($card, 6, '2026-10-22 09:00:00');

    expect(remaining($card))->toBe(19.0); // 20 + 6 - 7
});

test('reset on: unused liters do not carry over', function () {
    $card = QrCard::factory()->for(VehicleType::factory()->quota(20))->create();
    fuel($card, 2, '2026-10-21 10:00:00');

    expect(remaining($card))->toBe(20.0);
});

test('reset off: the quota is one pool for the whole validity period', function () {
    $card = QrCard::factory()->resetOff()->for(VehicleType::factory()->quota(20))->create(['issued_at' => '2026-09-01 07:00:00']);

    fuel($card, 5, '2026-09-10 10:00:00');
    fuel($card, 7, '2026-10-22 08:00:00');
    topUp($card, 3, '2026-09-15 09:00:00');

    expect(remaining($card))->toBe(11.0); // 20 + 3 - 12
});

test('a lifetime card with reset off stays depleted until it is topped up', function () {
    $card = QrCard::factory()->resetOff()->for(VehicleType::factory()->quota(20))->create();
    fuel($card, 20, '2026-10-01 10:00:00');

    expect(remaining($card))->toBe(0.0);

    $query = app(QuotaService::class)->query();
    app(QuotaService::class)->applyState($query, 'depleted');
    expect($query->count())->toBe(1);

    // Time passing does not refill a pool.
    $this->travelTo('2026-11-30 09:00:00');
    expect(remaining($card))->toBe(0.0);

    topUp($card, 15, '2026-11-30 09:05:00');
    expect(remaining($card))->toBe(15.0);
});

test('a top-up with reset on counts for the current period only', function () {
    $card = QrCard::factory()->for(VehicleType::factory()->quota(5))->create();
    topUp($card, 6, '2026-10-22 09:00:00');

    expect(remaining($card))->toBe(11.0); // 5 + 6 for this period

    $this->travelTo('2026-10-23 00:00:01');
    expect(remaining($card))->toBe(5.0);  // next period: back to the base quota
});

test('a top-up with reset off is added to the pool for good', function () {
    $card = QrCard::factory()->resetOff()->for(VehicleType::factory()->quota(5))->create();
    topUp($card, 6, '2026-10-22 09:00:00');

    $this->travelTo('2026-10-30 09:00:00');

    expect(remaining($card))->toBe(11.0);
});

test('the quota is always the vehicle type current quota', function () {
    $type = VehicleType::factory()->quota(20)->create();
    $cards = QrCard::factory()->count(3)->for($type)->create();
    fuel($cards[0], 4, '2026-10-22 08:00:00');

    $type->update(['quota_liters' => 30]);

    expect(remaining($cards[0]))->toBe(26.0)
        ->and(remaining($cards[1]))->toBe(30.0)
        ->and(remaining($cards[2]))->toBe(30.0);
});

test('changing the reset period recalculates remaining from the new period start without rewriting history', function () {
    $card = QrCard::factory()->for(VehicleType::factory()->quota(20))->create();
    fuel($card, 8, '2026-10-19 08:00:00'); // Monday
    fuel($card, 5, '2026-10-22 08:00:00'); // today

    expect(remaining($card))->toBe(15.0);

    setSettings(['reset_period' => 'week']);
    expect(remaining($card))->toBe(7.0);   // 20 - 8 - 5, history untouched

    setSettings(['reset_period' => 'month']);
    expect(remaining($card))->toBe(7.0);

    expect(FuelTransaction::count())->toBe(2);
});

test('changing the reset time moves the period boundary at once', function () {
    $card = QrCard::factory()->for(VehicleType::factory()->quota(20))->create();
    fuel($card, 5, '2026-10-22 03:00:00');

    expect(remaining($card))->toBe(15.0);

    setSettings(['reset_time' => '06:00']); // today's period now started at 06:00, after that fueling
    expect(remaining($card))->toBe(20.0);
});

test('state filters: expired, depleted and low quota', function () {
    $type = VehicleType::factory()->quota(10)->create();
    $depleted = QrCard::factory()->for($type)->create();
    $low = QrCard::factory()->for($type)->create();
    $fine = QrCard::factory()->for($type)->create();
    $expired = QrCard::factory()->for($type)->expired()->create();
    fuel($depleted, 10, '2026-10-22 08:00:00');
    fuel($low, 8.5, '2026-10-22 08:00:00'); // 1.5 L left = 15% of quota, threshold 20%

    $ids = function (string $state) {
        $query = app(QuotaService::class)->query();
        app(QuotaService::class)->applyState($query, $state);

        return $query->pluck('qr_cards.id')->sort()->values()->all();
    };

    expect($ids('depleted'))->toBe([$depleted->id])
        ->and($ids('low'))->toBe([$low->id])
        ->and($ids('expired'))->toBe([$expired->id])
        ->and($ids('active'))->toBe(collect([$depleted, $low, $fine])->pluck('id')->sort()->values()->all());
});

test('counters match the four counter cards', function () {
    $type = VehicleType::factory()->quota(10)->create();
    $depleted = QrCard::factory()->for($type)->create();
    QrCard::factory()->for($type)->create();
    QrCard::factory()->for($type)->temporary(30)->create();
    QrCard::factory()->for($type)->expired()->create();
    fuel($depleted, 10, '2026-10-22 08:00:00');

    expect(app(QuotaService::class)->counters())->toBe([
        'active' => 3,
        'lifetime' => 2,
        'expired' => 1,
        'depleted' => 1,
    ]);
});
