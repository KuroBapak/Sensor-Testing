<?php

use App\Models\Role;
use App\Models\Site;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('requires permission to update site settings', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('admin.settings.update'), [
            'timezone' => 'Asia/Jakarta',
            'settings' => [
                'settle_seconds' => 60,
                'window_minutes' => 5,
                'rfid_poll_seconds' => 10,
                'eval_grace_minutes' => 2,
                'reading_retention_days' => 90,
                'position_max_age_minutes' => 10,
                'geofence_exit_confirm_readings' => 3,
            ],
        ])
        ->assertForbidden();
});

it('updates site settings successfully', function () {
    $role = Role::create(['name' => 'Manager', 'is_system' => false]);
    $role->permissions()->create(['permission_key' => 'settings.manage']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $site = Site::create([
        'name' => 'Test Site',
        'timezone' => 'UTC',
        'settings' => [],
    ]);

    actingAs($user)
        ->post(route('admin.settings.update'), [
            'timezone' => 'Asia/Jakarta',
            'settings' => [
                'settle_seconds' => 60,
                'window_minutes' => 5,
                'rfid_poll_seconds' => 10,
                'eval_grace_minutes' => 2,
                'reading_retention_days' => 90,
                'position_max_age_minutes' => 10,
                'geofence_exit_confirm_readings' => 3,
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Site settings updated successfully.');

    $site->refresh();
    expect($site->timezone)->toBe('Asia/Jakarta');
    expect($site->settings['settle_seconds'])->toBe(60);
});
