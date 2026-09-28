<?php

use App\Models\AnomalyLog;
use App\Models\Role;
use App\Models\Site;
use App\Models\Tank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->site = Site::create([
        'name' => 'Main Facility',
        'timezone' => 'UTC',
    ]);

    $this->role = Role::create(['name' => 'Operator', 'is_system' => false]);
    $this->role->permissions()->create(['permission_key' => 'alarms.manage']);
    $this->user = User::factory()->create(['role_id' => $this->role->id]);

    $this->tank = Tank::create([
        'site_id' => $this->site->id,
        'tank_id' => 1,
        'name' => 'Main Tank 1',
        'division' => 'main_tank',
        'capacity_liters' => 50000,
    ]);

    $this->anomaly = AnomalyLog::create([
        'anomaly_type' => 'sudden_change_theft',
        'tank_id' => $this->tank->tank_id,
        'volume_diff' => -600.00,
        'anomaly_time' => now()->subHours(2),
        'status_investigasi' => 'open',
    ]);
});

it('requires authentication to mark an alarm as read', function () {
    $this->postJson(route('api.v1.alerts.read', $this->anomaly))
        ->assertUnauthorized();
});

it('marks an alarm as read and transitions status from open to investigating', function () {
    Sanctum::actingAs($this->user);

    $response = $this->postJson(route('api.v1.alerts.read', $this->anomaly))
        ->assertOk()
        ->assertJsonPath('data.status_investigasi', 'investigating');

    $this->assertDatabaseHas('anomaly_reads', [
        'anomaly_id' => $this->anomaly->id,
        'user_id' => $this->user->id,
    ]);

    expect($this->anomaly->fresh()->status_investigasi)->toBe('investigating');
    expect($this->anomaly->isReadBy($this->user))->toBeTrue();
});

it('supports patch method for marking alarms as read', function () {
    Sanctum::actingAs($this->user);

    $this->patchJson(route('api.v1.alerts.read', $this->anomaly))
        ->assertOk();

    expect($this->anomaly->isReadBy($this->user))->toBeTrue();
});

it('updates alarm status to resolved with note and resolver metadata', function () {
    Sanctum::actingAs($this->user);

    $this->putJson(route('api.v1.alerts.status', $this->anomaly), [
        'status' => 'resolved',
        'resolution_note' => 'Recalibrated radar sensor and verified dipstick.',
    ])
        ->assertOk()
        ->assertJsonPath('data.status_investigasi', 'resolved')
        ->assertJsonPath('data.resolution_note', 'Recalibrated radar sensor and verified dipstick.')
        ->assertJsonPath('data.resolved_by.id', $this->user->id);

    $fresh = $this->anomaly->fresh();
    expect($fresh->status_investigasi)->toBe('resolved');
    expect($fresh->resolved_by)->toBe($this->user->id);
    expect($fresh->resolved_at)->not->toBeNull();
});

it('clears resolved metadata when re-opening an alarm', function () {
    Sanctum::actingAs($this->user);

    // First resolve it
    $this->anomaly->update([
        'status_investigasi' => 'resolved',
        'resolved_by' => $this->user->id,
        'resolved_at' => now(),
    ]);

    // Then re-open to investigating
    $this->putJson(route('api.v1.alerts.status', $this->anomaly), [
        'status' => 'investigating',
        'resolution_note' => 'Reopening due to repeated sensor drift.',
    ])
        ->assertOk()
        ->assertJsonPath('data.status_investigasi', 'investigating');

    $fresh = $this->anomaly->fresh();
    expect($fresh->status_investigasi)->toBe('investigating');
    expect($fresh->resolved_by)->toBeNull();
    expect($fresh->resolved_at)->toBeNull();
});
