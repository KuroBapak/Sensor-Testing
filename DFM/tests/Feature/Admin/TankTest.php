<?php

use App\Models\Role;
use App\Models\Site;
use App\Models\Tank;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('requires permission to manage tanks', function () {
    $user = User::factory()->create(); // no role

    actingAs($user)->get(route('admin.tanks.index'))->assertForbidden();
    actingAs($user)->post(route('admin.tanks.store'))->assertForbidden();
});

it('lists tanks successfully', function () {
    $role = Role::create(['name' => 'Manager', 'is_system' => false]);
    $role->permissions()->create(['permission_key' => 'tanks.manage']);
    $user = User::factory()->create(['role_id' => $role->id]);

    actingAs($user)
        ->get(route('admin.tanks.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/tanks/index')
            ->has('tanks')
            ->has('devices')
        );
});

it('creates a new tank', function () {
    $role = Role::create(['name' => 'Manager', 'is_system' => false]);
    $role->permissions()->create(['permission_key' => 'tanks.manage']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $site = Site::create([
        'name' => 'Test Site',
        'timezone' => 'UTC',
    ]);

    actingAs($user)
        ->post(route('admin.tanks.store'), [
            'name' => 'New Browser Tank',
            'division' => 'browser_tank',
            'capacity_liters' => 5000,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Tank created successfully.');

    $this->assertDatabaseHas('tanks', [
        'name' => 'New Browser Tank',
        'division' => 'browser_tank',
        'capacity_liters' => 5000,
        'site_id' => $site->id,
    ]);
});

it('updates a tank', function () {
    $role = Role::create(['name' => 'Manager', 'is_system' => false]);
    $role->permissions()->create(['permission_key' => 'tanks.manage']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $site = Site::create([
        'name' => 'Test Site',
        'timezone' => 'UTC',
    ]);

    $tank = Tank::create([
        'site_id' => $site->id,
        'name' => 'Old Tank',
        'division' => 'browser_tank',
        'capacity_liters' => 1000,
    ]);

    actingAs($user)
        ->put(route('admin.tanks.update', $tank), [
            'name' => 'Updated Tank',
            'division' => 'main_tank',
            'capacity_liters' => 10000,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Tank updated successfully.');

    $this->assertDatabaseHas('tanks', [
        'tank_id' => $tank->tank_id,
        'name' => 'Updated Tank',
        'division' => 'main_tank',
        'capacity_liters' => 10000,
    ]);
});

it('deletes a tank', function () {
    $role = Role::create(['name' => 'Manager', 'is_system' => false]);
    $role->permissions()->create(['permission_key' => 'tanks.manage']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $site = Site::create([
        'name' => 'Test Site',
        'timezone' => 'UTC',
    ]);

    $tank = Tank::create([
        'site_id' => $site->id,
        'name' => 'To Delete',
        'division' => 'browser_tank',
        'capacity_liters' => 1000,
    ]);

    actingAs($user)
        ->delete(route('admin.tanks.destroy', $tank))
        ->assertRedirect()
        ->assertSessionHas('success', 'Tank deleted successfully.');

    $this->assertSoftDeleted('tanks', [
        'tank_id' => $tank->tank_id,
    ]);
});
