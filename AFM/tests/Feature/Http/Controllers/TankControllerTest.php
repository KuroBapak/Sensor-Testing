<?php

use App\Models\Tank;
use App\Models\TankReading;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // Thursday 22 Oct 2026, 14:30 WIB.
    $this->travelTo('2026-10-22 14:30:00');
});

test('guests are redirected to the login page', function () {
    $this->get(route('tank.index'))->assertRedirect(route('login'));
});

test('shows each tank with its latest level split into fuel and water, in percent and liters', function () {
    $tank = Tank::factory()->create(['name' => 'Tangki 1', 'capacity_liters' => 2300]);
    TankReading::factory()->for($tank)->create(['read_at' => '2026-10-22 14:00:00', 'total_pct' => 80, 'fuel_pct' => 60, 'water_pct' => 20]);

    $this->actingAs(admin())
        ->get(route('tank.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tank/index')
            ->where('tanks.0.name', 'Tangki 1')
            ->where('tanks.0.capacity', 2300)
            ->where('tanks.0.latest.total_pct', 80)
            ->where('tanks.0.latest.fuel_pct', 60)
            ->where('tanks.0.latest.water_pct', 20)
            ->where('tanks.0.latest.empty_pct', 20)
            ->where('tanks.0.latest.total_liters', 1840)
            ->where('tanks.0.latest.fuel_liters', 1380)
            ->where('tanks.0.latest.water_liters', 460)
            ->etc());
});

test('lists the last five hours of readings, newest first, with total equal to fuel plus water', function () {
    $tank = Tank::factory()->create();
    foreach ([8, 9, 10, 11, 12, 13, 14] as $hour) {
        TankReading::factory()->for($tank)->create([
            'read_at' => sprintf('2026-10-22 %02d:00:00', $hour),
            'total_pct' => 50 + $hour,
            'fuel_pct' => 48 + $hour,
            'water_pct' => 2,
        ]);
    }

    $recent = $this->actingAs(admin())->get(route('tank.index'))->inertiaProps('tanks.0.recent');

    expect(array_column($recent, 'read_at'))->toBe([
        '2026-10-22T14:00:00+07:00',
        '2026-10-22T13:00:00+07:00',
        '2026-10-22T12:00:00+07:00',
        '2026-10-22T11:00:00+07:00',
        '2026-10-22T10:00:00+07:00',
    ]);
    foreach ($recent as $reading) {
        expect($reading['total_pct'])->toBe($reading['fuel_pct'] + $reading['water_pct']);
    }
});

test('ignores readings from the future', function () {
    $tank = Tank::factory()->create();
    TankReading::factory()->for($tank)->create(['read_at' => '2026-10-22 14:00:00', 'total_pct' => 60, 'fuel_pct' => 58, 'water_pct' => 2]);
    TankReading::factory()->for($tank)->create(['read_at' => '2026-10-22 15:00:00', 'total_pct' => 90, 'fuel_pct' => 88, 'water_pct' => 2]);

    $this->actingAs(admin())
        ->get(route('tank.index'))
        ->assertInertia(fn (Assert $page) => $page->where('tanks.0.latest.total_pct', 60)->etc());
});

test('shows a tank without readings as empty instead of failing', function () {
    Tank::factory()->create();

    $this->actingAs(admin())
        ->get(route('tank.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('tanks.0.latest', null)
            ->has('tanks.0.recent', 0)
            ->has('tanks.0.chart', 0)
            ->etc());
});

test('orders tanks by id so Tank 1 is always first', function () {
    Tank::factory()->create(['name' => 'Tangki 1', 'capacity_liters' => 2300]);
    Tank::factory()->create(['name' => 'Tangki 2', 'capacity_liters' => 2800]);

    $tanks = $this->actingAs(admin())->get(route('tank.index'))->inertiaProps('tanks');

    expect(array_column($tanks, 'name'))->toBe(['Tangki 1', 'Tangki 2'])
        ->and(array_column($tanks, 'capacity'))->toEqual([2300, 2800]);
});
