<?php

use App\Models\Role;
use App\Models\Site;
use App\Models\Tank;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->site = Site::create([
        'name' => 'Main Site',
        'timezone' => 'UTC',
    ]);

    $this->role = Role::create(['name' => 'Fuel Manager', 'is_system' => false]);
    $this->role->permissions()->create(['permission_key' => 'vendor_fill.create']);
    $this->user = User::factory()->create(['role_id' => $this->role->id]);

    $this->mainTank = Tank::create([
        'site_id' => $this->site->id,
        'tank_id' => 1,
        'name' => 'Main Tank 1',
        'division' => 'main_tank',
        'capacity_liters' => 60000,
    ]);
});

it('requires permission to access vendor fills', function () {
    $unprivileged = User::factory()->create();

    actingAs($unprivileged)->get(route('admin.vendor-fills.index'))->assertForbidden();
    actingAs($unprivileged)->post(route('admin.vendor-fills.store'))->assertForbidden();
});

it('renders vendor fills page with data', function () {
    Transaction::create([
        'transfer_type' => 'vendor_fill',
        'tank_id' => $this->mainTank->tank_id,
        'main_tank_id' => $this->mainTank->tank_id,
        'liters' => 15000,
        'started_at' => now()->subHour(),
        'ended_at' => now(),
        'sync_status' => 'live',
        'entered_by' => $this->user->id,
    ]);

    actingAs($this->user)
        ->get(route('admin.vendor-fills.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/vendor-fills/index')
            ->has('vendorFills.data', 1)
            ->has('mainTanks', 1)
        );
});

it('records a new vendor fill transaction successfully', function () {
    $started = now()->subMinutes(45)->format('Y-m-d H:i:s');
    $ended = now()->format('Y-m-d H:i:s');

    actingAs($this->user)
        ->post(route('admin.vendor-fills.store'), [
            'tank_id' => $this->mainTank->tank_id,
            'liters' => 20000.50,
            'started_at' => $started,
            'ended_at' => $ended,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Vendor fill recorded.');

    $this->assertDatabaseHas('transactions', [
        'transfer_type' => 'vendor_fill',
        'tank_id' => $this->mainTank->tank_id,
        'main_tank_id' => $this->mainTank->tank_id,
        'liters' => 20000.50,
        'entered_by' => $this->user->id,
        'sync_status' => 'live',
    ]);
});

it('validates vendor fill required fields', function () {
    actingAs($this->user)
        ->post(route('admin.vendor-fills.store'), [
            'tank_id' => '',
            'liters' => -5,
            'started_at' => 'invalid-date',
            'ended_at' => 'invalid-date',
        ])
        ->assertSessionHasErrors(['tank_id', 'liters', 'started_at', 'ended_at']);
});
