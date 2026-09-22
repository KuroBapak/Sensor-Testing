<?php

use App\Models\Role;
use App\Models\Site;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('requires permission to update sensitivity', function () {
    $user = User::factory()->create(); // no role

    actingAs($user)
        ->post(route('admin.sensitivity.update'), [
            'browser_tank_threshold' => 10,
            'fuel_tanker_threshold' => 10,
            'main_tank_threshold' => 2,
            'tolerance_pct' => 1.0,
        ])
        ->assertForbidden();
});

it('updates site sensitivity settings successfully', function () {
    $role = Role::create(['name' => 'Manager', 'is_system' => false]);
    $role->permissions()->create(['permission_key' => 'sensitivity.manage']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $site = Site::create([
        'name' => 'Test Site',
        'timezone' => 'UTC',
        'settings' => [],
    ]);

    actingAs($user)
        ->post(route('admin.sensitivity.update'), [
            'browser_tank_threshold' => 3.5,
            'fuel_tanker_threshold' => 1.2,
            'main_tank_threshold' => 10,
            'tolerance_pct' => 2.0,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Sensitivity settings updated successfully.');

    $site->refresh();
    expect($site->settings['sensitivity']['browser_tank_threshold'])->toBe(3.5);
    expect($site->settings['sensitivity']['fuel_tanker_threshold'])->toBe(1.2);
});
