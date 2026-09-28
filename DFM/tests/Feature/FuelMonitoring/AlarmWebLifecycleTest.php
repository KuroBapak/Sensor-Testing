<?php

use App\Models\AnomalyLog;
use App\Models\Role;
use App\Models\Site;
use App\Models\Tank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->site = Site::create(['name' => 'Main Site', 'timezone' => 'UTC']);

    $this->role = Role::create(['name' => 'Operator', 'is_system' => false]);
    $this->role->permissions()->create(['permission_key' => 'alarms.view']);
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
        'anomaly_time' => now()->subHour(),
        'status_investigasi' => 'open',
    ]);
});

it('marks an alarm as read through the web route', function () {
    actingAs($this->user)
        ->postJson(route('fuel-monitoring.alarms.read', $this->anomaly))
        ->assertOk()
        ->assertJsonPath('data.status_investigasi', 'investigating');

    $this->assertDatabaseHas('anomaly_reads', [
        'anomaly_id' => $this->anomaly->id,
        'user_id' => $this->user->id,
    ]);
});

it('resolves an alarm through the web route', function () {
    actingAs($this->user)
        ->putJson(route('fuel-monitoring.alarms.status', $this->anomaly), [
            'status' => 'false_positive',
        ])
        ->assertOk()
        ->assertJsonPath('data.status_investigasi', 'false_positive');

    expect($this->anomaly->fresh()->resolved_by)->toBe($this->user->id);
});

it('forbids alarm lifecycle access without permission', function () {
    $plain = User::factory()->create();

    actingAs($plain)
        ->postJson(route('fuel-monitoring.alarms.read', $this->anomaly))
        ->assertForbidden();
});
