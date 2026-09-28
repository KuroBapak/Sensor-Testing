<?php

use App\Models\Geofence;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->site = Site::create([
        'name' => 'Main Site',
        'timezone' => 'UTC',
    ]);

    $this->role = Role::create(['name' => 'Geofence Admin', 'is_system' => false]);
    $this->role->permissions()->create(['permission_key' => 'geofences.manage']);
    $this->user = User::factory()->create(['role_id' => $this->role->id]);
});

it('renders the geofence editor page', function () {
    Geofence::create([
        'site_id' => $this->site->id,
        'name' => 'Depot Zone',
        'applies_to' => 'fuel_tanker',
        'is_active' => true,
        'coordinates' => [[-2.59, 117.99], [-2.59, 118.01], [-2.61, 118.01], [-2.61, 117.99]],
    ]);

    actingAs($this->user)
        ->get(route('admin.geofences.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/geofences/index')
            ->has('geofences', 1)
        );
});

it('forbids geofence access without permission', function () {
    $plain = User::factory()->create();

    actingAs($plain)->get(route('admin.geofences.index'))->assertForbidden();
    actingAs($plain)->post('/admin/geofences')->assertForbidden();
});

it('creates a geofence polygon via the web route', function () {
    actingAs($this->user)
        ->postJson('/admin/geofences', [
            'name' => 'New Zone',
            'applies_to' => 'browser_tank,fuel_tanker',
            'is_active' => true,
            'coordinates' => [[-2.59, 117.99], [-2.59, 118.01], [-2.61, 118.01]],
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'New Zone');

    $this->assertDatabaseHas('geofences', ['name' => 'New Zone', 'site_id' => $this->site->id]);
});

it('rejects a geofence with fewer than three vertices', function () {
    actingAs($this->user)
        ->postJson('/admin/geofences', [
            'name' => 'Bad Zone',
            'applies_to' => 'fuel_tanker',
            'coordinates' => [[-2.59, 117.99], [-2.59, 118.01]],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('coordinates');
});

it('updates an existing geofence', function () {
    $geofence = Geofence::create([
        'site_id' => $this->site->id,
        'name' => 'Old Name',
        'applies_to' => 'fuel_tanker',
        'is_active' => true,
        'coordinates' => [[-2.59, 117.99], [-2.59, 118.01], [-2.61, 118.01]],
    ]);

    actingAs($this->user)
        ->putJson("/admin/geofences/{$geofence->id}", ['name' => 'Renamed Zone'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Renamed Zone');
});

it('deletes a geofence', function () {
    $geofence = Geofence::create([
        'site_id' => $this->site->id,
        'name' => 'Doomed Zone',
        'applies_to' => 'fuel_tanker',
        'is_active' => true,
        'coordinates' => [[-2.59, 117.99], [-2.59, 118.01], [-2.61, 118.01]],
    ]);

    actingAs($this->user)
        ->deleteJson("/admin/geofences/{$geofence->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('geofences', ['id' => $geofence->id]);
});
