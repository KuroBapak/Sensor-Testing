<?php

use App\Models\RfidTag;
use App\Models\Role;
use App\Models\Site;
use App\Models\Tank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->site = Site::create([
        'name' => 'Main Facility',
        'timezone' => 'UTC',
        'rfid_list_version' => 1,
    ]);

    $this->role = Role::create(['name' => 'RFID Admin', 'is_system' => false]);
    $this->role->permissions()->create(['permission_key' => 'rfid.manage']);
    $this->user = User::factory()->create(['role_id' => $this->role->id]);

    $this->tank = Tank::create([
        'site_id' => $this->site->id,
        'tank_id' => 10,
        'name' => 'Browser Tank 10',
        'division' => 'browser_tank',
        'capacity_liters' => 5000,
    ]);
});

it('requires permission to manage rfid tags', function () {
    $unprivileged = User::factory()->create();

    actingAs($unprivileged)->get(route('admin.rfid.index'))->assertForbidden();
    actingAs($unprivileged)->post(route('admin.rfid.store'))->assertForbidden();
});

it('renders rfid page with tags and tanks', function () {
    RfidTag::create([
        'tag_id' => 'RFID-TAG-001',
        'tank_id' => $this->tank->tank_id,
        'sector' => 'Pit Area',
        'status' => 'active',
    ]);

    actingAs($this->user)
        ->get(route('admin.rfid.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/rfid/index')
            ->has('tags', 1)
            ->has('tanks', 1)
        );
});

it('registers a new rfid tag and bumps rfid_list_version', function () {
    actingAs($this->user)
        ->post(route('admin.rfid.store'), [
            'tag_id' => 'RFID-TAG-002',
            'tank_id' => $this->tank->tank_id,
            'sector' => 'Crusher 2',
            'status' => 'active',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'RFID tag registered.');

    $this->assertDatabaseHas('rfid_tags', [
        'tag_id' => 'RFID-TAG-002',
        'tank_id' => $this->tank->tank_id,
        'status' => 'active',
    ]);

    expect($this->site->fresh()->rfid_list_version)->toBe(2);
});

it('updates an existing rfid tag and bumps version', function () {
    $tag = RfidTag::create([
        'tag_id' => 'RFID-TAG-003',
        'status' => 'active',
    ]);

    actingAs($this->user)
        ->put(route('admin.rfid.update', $tag), [
            'tank_id' => $this->tank->tank_id,
            'sector' => 'Workshop North',
            'status' => 'blocked',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'RFID tag updated.');

    $this->assertDatabaseHas('rfid_tags', [
        'tag_id' => 'RFID-TAG-003',
        'sector' => 'Workshop North',
        'status' => 'blocked',
    ]);

    expect($this->site->fresh()->rfid_list_version)->toBe(2);
});

it('deletes an rfid tag and bumps version', function () {
    $tag = RfidTag::create([
        'tag_id' => 'RFID-TAG-004',
        'status' => 'blocked',
    ]);

    actingAs($this->user)
        ->delete(route('admin.rfid.destroy', $tag))
        ->assertRedirect()
        ->assertSessionHas('success', 'RFID tag deleted.');

    $this->assertDatabaseMissing('rfid_tags', [
        'tag_id' => 'RFID-TAG-004',
    ]);

    expect($this->site->fresh()->rfid_list_version)->toBe(2);
});
