<?php

use App\Enums\ResetPeriod;
use App\Services\PeriodService;
use Carbon\CarbonImmutable;

function periodStart(ResetPeriod $period, string $at): string
{
    return app(PeriodService::class)
        ->startOf($period, CarbonImmutable::parse($at, 'Asia/Jakarta'))
        ->toDateTimeString();
}

test('a day starts at the reset time', function () {
    setSettings(['reset_time' => '06:00']);

    expect(periodStart(ResetPeriod::Day, '2026-10-22 05:59'))->toBe('2026-10-21 06:00:00')
        ->and(periodStart(ResetPeriod::Day, '2026-10-22 06:00'))->toBe('2026-10-22 06:00:00')
        ->and(periodStart(ResetPeriod::Day, '2026-10-22 23:30'))->toBe('2026-10-22 06:00:00');
});

test('the default day starts at midnight', function () {
    expect(periodStart(ResetPeriod::Day, '2026-10-22 14:30'))->toBe('2026-10-22 00:00:00');
});

test('a week starts on the configured weekday (Monday by default)', function () {
    // 22 Oct 2026 is a Thursday.
    expect(periodStart(ResetPeriod::Week, '2026-10-22 14:30'))->toBe('2026-10-19 00:00:00');

    setSettings(['week_start_day' => 3]); // Wednesday
    expect(periodStart(ResetPeriod::Week, '2026-10-22 14:30'))->toBe('2026-10-21 00:00:00');

    setSettings(['week_start_day' => 7]); // Sunday
    expect(periodStart(ResetPeriod::Week, '2026-10-22 14:30'))->toBe('2026-10-18 00:00:00');
});

test('a week start day honours the reset time', function () {
    setSettings(['week_start_day' => 4, 'reset_time' => '08:00']); // Thursday 08:00

    expect(periodStart(ResetPeriod::Week, '2026-10-22 07:59'))->toBe('2026-10-15 08:00:00')
        ->and(periodStart(ResetPeriod::Week, '2026-10-22 08:00'))->toBe('2026-10-22 08:00:00');
});

test('a month starts on the configured day (the 1st by default)', function () {
    expect(periodStart(ResetPeriod::Month, '2026-10-22 14:30'))->toBe('2026-10-01 00:00:00');

    setSettings(['month_reset_day' => 15]);
    expect(periodStart(ResetPeriod::Month, '2026-10-22 14:30'))->toBe('2026-10-15 00:00:00')
        ->and(periodStart(ResetPeriod::Month, '2026-10-10 14:30'))->toBe('2026-09-15 00:00:00');
});

test('a month start honours the reset time on the reset day', function () {
    setSettings(['month_reset_day' => 15, 'reset_time' => '08:00']);

    expect(periodStart(ResetPeriod::Month, '2026-10-15 07:59'))->toBe('2026-09-15 08:00:00')
        ->and(periodStart(ResetPeriod::Month, '2026-10-15 08:00'))->toBe('2026-10-15 08:00:00');
});

test('the next period starts one period after the current one', function () {
    $periods = app(PeriodService::class);
    $at = CarbonImmutable::parse('2026-10-22 14:30', 'Asia/Jakarta');

    expect($periods->nextStart(ResetPeriod::Day, $at)->toDateTimeString())->toBe('2026-10-23 00:00:00')
        ->and($periods->nextStart(ResetPeriod::Week, $at)->toDateTimeString())->toBe('2026-10-26 00:00:00')
        ->and($periods->nextStart(ResetPeriod::Month, $at)->toDateTimeString())->toBe('2026-11-01 00:00:00');
});

test('periods follow the time zone from settings', function () {
    setSettings(['timezone' => 'Asia/Makassar']); // WITA, one hour ahead of WIB

    // 23:30 WIB on the 22nd is already 00:30 WITA on the 23rd.
    $start = app(PeriodService::class)->startOf(ResetPeriod::Day, CarbonImmutable::parse('2026-10-22 23:30', 'Asia/Jakarta'));

    expect($start->toDateTimeString())->toBe('2026-10-23 00:00:00')
        ->and($start->timezoneName)->toBe('Asia/Makassar');
});
