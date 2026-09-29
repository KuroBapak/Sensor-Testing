<?php

use App\Models\HardwareDevice;
use App\Models\RfidTag;
use App\Models\Site;
use App\Models\Tank;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->site = Site::create(['name' => 'Main Site', 'timezone' => 'UTC']);

    $this->tank = Tank::create([
        'site_id' => $this->site->id,
        'tank_id' => 1,
        'name' => 'Main Tank 1',
        'division' => 'main_tank',
        'capacity_liters' => 50000,
    ]);

    $this->plainToken = 'test-token-123';

    $this->device = HardwareDevice::create([
        'device_id' => 'RUT956-001',
        'device_type' => 'main_tank_atg',
        'tank_id' => $this->tank->tank_id,
        'site_id' => $this->site->id,
        'status' => 'active',
        'api_token_hash' => hash('sha256', $this->plainToken),
    ]);
});

test('emergency RFID check returns allow true for active tag', function () {
    RfidTag::create([
        'tag_id' => 'RFID_12345',
        'tank_id' => $this->tank->tank_id,
        'status' => 'active',
        'sector' => 'A1',
    ]);

    $response = $this->withToken($this->plainToken)->postJson('/api/v1/auth/check', [
        'tag_uid' => 'RFID_12345',
    ]);

    $response->assertOk()
        ->assertJson([
            'allow' => true,
            'reason' => 'Tag is valid',
            'tank_id' => $this->tank->tank_id,
            'sector' => 'A1',
        ]);
});

test('emergency RFID check returns allow false for blocked tag', function () {
    RfidTag::create([
        'tag_id' => 'RFID_BLOCKED',
        'tank_id' => $this->tank->tank_id,
        'status' => 'blocked',
        'sector' => 'A1',
    ]);

    $response = $this->withToken($this->plainToken)->postJson('/api/v1/auth/check', [
        'tag_uid' => 'RFID_BLOCKED',
    ]);

    $response->assertOk()
        ->assertJson([
            'allow' => false,
            'reason' => 'Tag is blocked',
        ]);
});

test('emergency RFID check returns allow false for unregistered tag', function () {
    $response = $this->withToken($this->plainToken)->postJson('/api/v1/auth/check', [
        'tag_uid' => 'RFID_UNKNOWN',
    ]);

    $response->assertOk()
        ->assertJson([
            'allow' => false,
            'reason' => 'Tag not registered',
        ]);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
