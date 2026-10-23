<?php

use App\Models\FuelTransaction;
use App\Models\QrCard;
use App\Models\VehicleType;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // Thursday 22 Oct 2026, 14:30 WIB; the week started on Monday the 19th.
    $this->travelTo('2026-10-22 14:30:00');
});

/** Two internal cards of one type, one of another, and one external card. */
function usageFixture(): array
{
    $granMax = VehicleType::factory()->quota(20)->create(['name' => 'Gran Max']);
    $hilux = VehicleType::factory()->quota(40)->create(['name' => 'Hilux']);

    $a1 = QrCard::factory()->for($granMax)->create(['plate_number' => 'B 1 AAA']);
    $a2 = QrCard::factory()->for($granMax)->create(['plate_number' => 'B 2 BBB']);
    $h1 = QrCard::factory()->for($hilux)->create(['plate_number' => 'B 3 CCC']);
    $e1 = QrCard::factory()->external()->for($granMax)->create(['plate_number' => 'B 4 EXT']);

    FuelTransaction::factory()->for($a1)->create(['liters' => 5, 'dispensed_at' => '2026-10-22 08:00:00']);
    FuelTransaction::factory()->for($a1)->create(['liters' => 7, 'dispensed_at' => '2026-10-19 08:00:00']);
    FuelTransaction::factory()->for($a2)->create(['liters' => 3, 'dispensed_at' => '2026-10-22 09:00:00']);
    FuelTransaction::factory()->for($h1)->create(['liters' => 11, 'dispensed_at' => '2026-10-22 10:00:00']);
    FuelTransaction::factory()->for($e1)->create(['liters' => 9, 'dispensed_at' => '2026-10-22 11:00:00']);

    return compact('a1', 'a2', 'h1', 'e1');
}

test('guests are redirected to the login page', function () {
    $this->get(route('usage.index'))->assertRedirect(route('login'));
    $this->getJson(route('usage.show', QrCard::factory()->create()))->assertUnauthorized();
});

test('shows internal cards for today grouped by vehicle type with subtotals', function () {
    usageFixture();

    $this->actingAs(admin())
        ->get(route('usage.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('usage/index')
            ->where('side', 'internal')
            ->where('period', 'day')
            ->where('totals.internal', 19)
            ->where('totals.external', 9)
            ->has('groups', 2)
            ->where('groups.0.vehicle_type', 'Gran Max')
            ->where('groups.0.subtotal', 8)
            ->has('groups.0.rows', 2)
            ->where('groups.0.rows.0.plate_number', 'B 1 AAA')
            ->where('groups.0.rows.0.used', 5)
            ->where('groups.0.rows.0.quota', 20)
            ->where('groups.0.rows.0.remaining', 15)
            ->where('groups.1.vehicle_type', 'Hilux')
            ->where('groups.1.subtotal', 11)
            ->etc());
});

test('sends a graph of the side total whose last bar equals the total shown', function () {
    usageFixture();

    $chart = $this->actingAs(admin())->get(route('usage.index'))->inertiaProps('chart');

    expect($chart)->toHaveCount(14)
        ->and(end($chart)['liters'])->toEqual(19)
        ->and(end($chart)['label'])->toBe('22/10');
});

test('the graph follows the chosen side and period', function () {
    usageFixture();
    $this->actingAs(admin());

    $external = $this->get(route('usage.index', ['side' => 'external']))->inertiaProps('chart');
    $weeks = $this->get(route('usage.index', ['period' => 'week']))->inertiaProps('chart');

    expect(end($external)['liters'])->toEqual(9)
        ->and($weeks)->toHaveCount(12)
        ->and(end($weeks)['liters'])->toEqual(26);
});

test('shows only external cards on the external side', function () {
    usageFixture();

    $this->actingAs(admin())
        ->get(route('usage.index', ['side' => 'external']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('side', 'external')
            ->has('groups', 1)
            ->where('groups.0.rows.0.plate_number', 'B 4 EXT')
            ->where('groups.0.subtotal', 9)
            ->etc());
});

test('the week selection widens the period without changing the reset period', function () {
    usageFixture();

    $this->actingAs(admin())
        ->get(route('usage.index', ['period' => 'week']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('period', 'week')
            ->where('totals.internal', 26)
            ->where('groups.0.rows.0.used', 12)
            // remaining still follows the reset period from settings (day)
            ->where('groups.0.rows.0.remaining', 15)
            ->where('site.resetPeriod', 'day')
            ->etc());
});

test('falls back to internal and day when the query is not recognised', function () {
    usageFixture();

    $this->actingAs(admin())
        ->get(route('usage.index', ['side' => 'rental', 'period' => 'decade']))
        ->assertInertia(fn (Assert $page) => $page->where('side', 'internal')->where('period', 'day')->etc());
});

test('keeps a deleted card in the rows and totals while it has usage in the period', function () {
    $cards = usageFixture();
    $cards['a2']->delete();

    $props = $this->actingAs(admin())->get(route('usage.index'))->inertiaProps();

    expect($props['totals']['internal'])->toEqual(19)
        ->and($props['groups'][0]['rows'][1]['deleted'])->toBeTrue()
        ->and($props['groups'][0]['rows'][1]['remaining'])->toBeNull();
});

test('returns the vehicle detail with day, week and month series and recent fuelings', function () {
    $cards = usageFixture();

    $this->actingAs(admin())
        ->getJson(route('usage.show', $cards['a1']))
        ->assertOk()
        ->assertJsonPath('card.plate_number', 'B 1 AAA')
        ->assertJsonPath('card.vehicle_type', 'Gran Max')
        ->assertJsonCount(14, 'series.day')
        ->assertJsonCount(12, 'series.week')
        ->assertJsonCount(6, 'series.month')
        ->assertJsonPath('series.day.13.liters', 5)
        ->assertJsonPath('series.week.11.liters', 12)
        ->assertJsonPath('fuelings.0.liters', 5)
        ->assertJsonPath('fuelings.1.liters', 7);
});

test('returns the detail of a deleted card for the history', function () {
    $cards = usageFixture();
    $cards['a1']->delete();

    $this->actingAs(admin())
        ->getJson(route('usage.show', $cards['a1']))
        ->assertOk()
        ->assertJsonPath('card.deleted', true);
});
