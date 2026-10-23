<?php

use App\Enums\CardCategory;
use App\Enums\ResetPeriod;
use App\Models\FuelTransaction;
use App\Models\QrCard;
use App\Models\VehicleType;
use App\Services\CardService;
use App\Services\UsageService;

beforeEach(function () {
    // Thursday 22 Oct 2026, 14:30 WIB.
    $this->travelTo('2026-10-22 14:30:00');
});

test('totals count only the chosen category and period', function () {
    $internal = QrCard::factory()->for(VehicleType::factory())->create();
    $external = QrCard::factory()->external()->for(VehicleType::factory())->create();
    FuelTransaction::factory()->for($internal)->create(['liters' => 12, 'dispensed_at' => '2026-10-22 08:00:00']);
    FuelTransaction::factory()->for($internal)->create(['liters' => 30, 'dispensed_at' => '2026-10-21 08:00:00']);
    FuelTransaction::factory()->for($external)->create(['liters' => 7, 'dispensed_at' => '2026-10-22 09:00:00']);

    [$from, $to] = app(UsageService::class)->range(ResetPeriod::Day);

    expect(app(UsageService::class)->total(CardCategory::Internal, $from, $to))->toBe(12.0)
        ->and(app(UsageService::class)->total(CardCategory::External, $from, $to))->toBe(7.0);
});

test('the week range runs from the week start to the next week start', function () {
    [$from, $to] = app(UsageService::class)->range(ResetPeriod::Week);

    expect($from->toDateTimeString())->toBe('2026-10-19 00:00:00')
        ->and($to->toDateTimeString())->toBe('2026-10-26 00:00:00');
});

test('a deleted card keeps counting in totals and rows while it has usage in the period', function () {
    $card = QrCard::factory()->for(VehicleType::factory())->create();
    FuelTransaction::factory()->for($card)->create(['liters' => 12, 'dispensed_at' => '2026-10-22 08:00:00']);
    app(CardService::class)->delete($card);

    [$from, $to] = app(UsageService::class)->range(ResetPeriod::Day);
    $rows = app(UsageService::class)->cardRows(CardCategory::Internal, $from, $to);

    expect(app(UsageService::class)->total(CardCategory::Internal, $from, $to))->toBe(12.0)
        ->and($rows->pluck('plate_number')->all())->toBe([$card->plate_number])
        ->and($rows->first()->used_in_period)->toBe(12.0);
});

test('a deleted card without usage in the period drops out of the rows', function () {
    $card = QrCard::factory()->for(VehicleType::factory())->create();
    $card->delete();

    [$from, $to] = app(UsageService::class)->range(ResetPeriod::Day);

    expect(app(UsageService::class)->cardRows(CardCategory::Internal, $from, $to))->toBeEmpty();
});

test('a card series buckets fuel into days and months, oldest first', function () {
    $card = QrCard::factory()->for(VehicleType::factory())->create();
    FuelTransaction::factory()->for($card)->create(['liters' => 5, 'dispensed_at' => '2026-10-22 08:00:00']);
    FuelTransaction::factory()->for($card)->create(['liters' => 7, 'dispensed_at' => '2026-10-22 09:00:00']);
    FuelTransaction::factory()->for($card)->create(['liters' => 3, 'dispensed_at' => '2026-10-20 09:00:00']);
    FuelTransaction::factory()->for($card)->create(['liters' => 9, 'dispensed_at' => '2026-09-05 09:00:00']);

    $days = collect(app(UsageService::class)->series($card, ResetPeriod::Day));
    $months = collect(app(UsageService::class)->series($card, ResetPeriod::Month));

    expect($days)->toHaveCount(14)
        ->and($days->last()['label'])->toBe('22/10')
        ->and($days->last()['liters'])->toBe(12.0)
        ->and($days->firstWhere('label', '20/10')['liters'])->toBe(3.0)
        ->and($months)->toHaveCount(6)
        ->and($months->last()['liters'])->toBe(15.0)
        ->and($months->firstWhere(fn ($month) => str_contains($month['label'], 'Sep'))['liters'])->toBe(9.0);
});

test('recent fuelings come newest first', function () {
    $card = QrCard::factory()->for(VehicleType::factory())->create();
    FuelTransaction::factory()->for($card)->create(['liters' => 1, 'dispensed_at' => '2026-10-20 08:00:00']);
    FuelTransaction::factory()->for($card)->create(['liters' => 2, 'dispensed_at' => '2026-10-22 08:00:00']);
    FuelTransaction::factory()->for($card)->create(['liters' => 3, 'dispensed_at' => '2026-10-21 08:00:00']);

    expect(app(UsageService::class)->recentFuelings($card)->pluck('liters')->all())->toBe([2.0, 3.0, 1.0]);
});

test('the category series sums every card per bucket and ends with the current period', function () {
    $first = QrCard::factory()->for(VehicleType::factory())->create();
    $second = QrCard::factory()->for(VehicleType::factory())->create();
    $external = QrCard::factory()->external()->for(VehicleType::factory())->create();
    FuelTransaction::factory()->for($first)->create(['liters' => 5, 'dispensed_at' => '2026-10-22 08:00:00']);
    FuelTransaction::factory()->for($second)->create(['liters' => 7, 'dispensed_at' => '2026-10-22 09:00:00']);
    FuelTransaction::factory()->for($first)->create(['liters' => 3, 'dispensed_at' => '2026-10-20 09:00:00']);
    FuelTransaction::factory()->for($external)->create(['liters' => 99, 'dispensed_at' => '2026-10-22 10:00:00']);

    $days = collect(app(UsageService::class)->totalSeries(CardCategory::Internal, ResetPeriod::Day));

    expect($days)->toHaveCount(14)
        ->and($days->last()['label'])->toBe('22/10')
        ->and($days->last()['liters'])->toBe(12.0)
        ->and($days->firstWhere('label', '20/10')['liters'])->toBe(3.0)
        ->and($days->firstWhere('label', '21/10')['liters'])->toBe(0.0);
});

test('the category series has 12 weekly and 6 monthly buckets', function () {
    $card = QrCard::factory()->for(VehicleType::factory())->create();
    FuelTransaction::factory()->for($card)->create(['liters' => 4, 'dispensed_at' => '2026-10-19 08:00:00']);
    FuelTransaction::factory()->for($card)->create(['liters' => 6, 'dispensed_at' => '2026-10-22 08:00:00']);

    $weeks = app(UsageService::class)->totalSeries(CardCategory::Internal, ResetPeriod::Week);
    $months = app(UsageService::class)->totalSeries(CardCategory::Internal, ResetPeriod::Month);

    expect($weeks)->toHaveCount(12)
        ->and(end($weeks)['liters'])->toBe(10.0)
        ->and($months)->toHaveCount(6)
        ->and(end($months)['label'])->toBe('Okt 2026');
});
