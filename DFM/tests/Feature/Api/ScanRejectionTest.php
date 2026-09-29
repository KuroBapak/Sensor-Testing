<?php

use App\Models\AnomalyLog;
use App\Models\HardwareDevice;
use App\Models\Site;
use App\Models\Tank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

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

test('scan rejection logs unauthorized_scan alarm and returns 201', function () {
    $eventId = (string) Str::uuid();

    $response = $this->withToken($this->plainToken)->postJson('/api/v1/scan-rejections', [
        'device_event_id' => $eventId,
        'device_id' => 'RUT956-001',
        'raw_tag_uid' => 'UNKNOWN_RFID_HEX_01',
        'reason' => 'Tag not found in local cache',
        'timestamp' => now()->toIso8601String(),
        'latitude' => -6.2088,
        'longitude' => 106.8456,
    ]);

    $response->assertCreated()
        ->assertJson([
            'status' => 'created',
        ]);

    $this->assertDatabaseHas('anomaly_logs', [
        'device_id' => 'RUT956-001',
        'device_event_id' => $eventId,
        'anomaly_type' => 'unauthorized_scan',
        'tag_id' => null, // Unknown tags don't exist in rfid_tags table
        'status_investigasi' => 'open',
    ]);

    $alarm = AnomalyLog::where('device_event_id', $eventId)->first();
    expect($alarm->meta['raw_tag_uid'])->toBe('UNKNOWN_RFID_HEX_01');
});

test('duplicate scan rejection is deduplicated and returns 200', function () {
    $eventId = (string) Str::uuid();

    // First request
    $this->withToken($this->plainToken)->postJson('/api/v1/scan-rejections', [
        'device_event_id' => $eventId,
        'device_id' => 'RUT956-001',
        'raw_tag_uid' => 'UNKNOWN_RFID_HEX_01',
        'reason' => 'Tag not found in local cache',
        'timestamp' => now()->toIso8601String(),
    ])->assertCreated();

    // Duplicate request
    $response = $this->withToken($this->plainToken)->postJson('/api/v1/scan-rejections', [
        'device_event_id' => $eventId,
        'device_id' => 'RUT956-001',
        'raw_tag_uid' => 'UNKNOWN_RFID_HEX_01',
        'reason' => 'Tag not found in local cache',
        'timestamp' => now()->toIso8601String(),
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'already_recorded',
        ]);

    expect(AnomalyLog::where('device_event_id', $eventId)->count())->toBe(1);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
