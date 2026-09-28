<?php

use App\Models\HardwareDevice;
use App\Models\Role;
use App\Models\Site;
use App\Models\Tank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->site = Site::create(['name' => 'Main Site', 'timezone' => 'UTC']);

    $this->role = Role::create(['name' => 'Viewer', 'is_system' => true]);

    $this->tank = Tank::create([
        'site_id' => $this->site->id,
        'tank_id' => 1,
        'name' => 'Main Tank 1',
        'division' => 'main_tank',
        'capacity_liters' => 50000,
    ]);

    $this->plainToken = 'edge-test-token-123';

    $this->device = HardwareDevice::create([
        'device_id' => 'RUT956-001',
        'device_type' => 'main_tank_atg',
        'tank_id' => $this->tank->tank_id,
        'site_id' => $this->site->id,
        'status' => 'active',
        'api_token_hash' => hash('sha256', $this->plainToken),
    ]);
});

function transactionPayload(): array
{
    return [
        'device_txn_id' => (string) Str::uuid(),
        'device_id' => 'RUT956-001',
        'transfer_type' => 'fill_to_main',
        'tank_id' => 1,
        'liters' => 1200.5,
        'started_at' => now()->subMinutes(10)->toIso8601String(),
        'ended_at' => now()->toIso8601String(),
    ];
}

it('rejects edge ingestion without a bearer token', function () {
    $this->postJson(route('api.v1.transactions'), transactionPayload())
        ->assertUnauthorized();
});

it('rejects an invalid device token', function () {
    $this->withToken('wrong-token')
        ->postJson(route('api.v1.transactions'), transactionPayload())
        ->assertUnauthorized();
});

it('rejects a retired or spare device token', function () {
    $this->device->update(['status' => 'spare']);

    $this->withToken($this->plainToken)
        ->postJson(route('api.v1.transactions'), transactionPayload())
        ->assertForbidden();
});

it('accepts a valid device token and records the transaction', function () {
    $this->withToken($this->plainToken)
        ->postJson(route('api.v1.transactions'), transactionPayload())
        ->assertCreated();

    $this->assertDatabaseHas('transactions', [
        'device_id' => 'RUT956-001',
        'tank_id' => 1,
        'liters' => 1200.5,
    ]);
});
