<?php

use App\Models\AnomalyLog;
use App\Models\ExportLog;
use App\Models\RfidTag;
use App\Models\Role;
use App\Models\Site;
use App\Models\Tank;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->role = Role::firstOrCreate(['name' => 'Super Admin'], ['is_system' => true]);
    $this->user = User::factory()->create(['role_id' => $this->role->id]);

    $this->site = Site::create([
        'name' => 'Main Facility',
        'timezone' => 'UTC',
    ]);

    $this->mainTank = Tank::create([
        'site_id' => $this->site->id,
        'tank_id' => 1,
        'name' => 'Main Tank 1',
        'division' => 'main_tank',
        'capacity_liters' => 50000,
    ]);

    $this->browserTank = Tank::create([
        'site_id' => $this->site->id,
        'tank_id' => 2,
        'name' => 'Browser Tank 01',
        'division' => 'browser_tank',
        'capacity_liters' => 5000,
    ]);
});

test('authenticated user can export main tank transactions report', function () {
    Transaction::create([
        'transfer_type' => 'vendor_fill',
        'tank_id' => $this->mainTank->tank_id,
        'main_tank_id' => $this->mainTank->tank_id,
        'liters' => 100,
        'started_at' => '2026-09-10 10:00:00',
        'ended_at' => '2026-09-10 10:30:00',
        'sync_status' => 'live',
    ]);

    actingAs($this->user)
        ->get('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-01&to=2026-09-30&format=standard')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertHeader('Content-Disposition', 'attachment; filename="main-tank-transactions_2026-09-01_to_2026-09-30_UTC.csv"');
});

test('export creates audit log entry', function () {
    Transaction::create([
        'transfer_type' => 'vendor_fill',
        'tank_id' => $this->mainTank->tank_id,
        'main_tank_id' => $this->mainTank->tank_id,
        'liters' => 100,
        'started_at' => '2026-09-10 10:00:00',
        'ended_at' => '2026-09-10 10:30:00',
        'sync_status' => 'live',
    ]);

    expect(ExportLog::count())->toBe(0);

    actingAs($this->user)
        ->get('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-01&to=2026-09-30&format=standard')
        ->assertOk();

    expect(ExportLog::count())->toBe(1);

    $log = ExportLog::first();
    expect($log->user_id)->toBe($this->user->id);
    expect($log->report_type)->toBe('main-tank-transactions');
    expect($log->format_preset)->toBe('standard');
});

test('export requires authentication', function () {
    $this->get('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-01&to=2026-09-30')
        ->assertStatus(403);
});

test('export validates date format', function () {
    actingAs($this->user)
        ->getJson('/api/v1/reports/main-tank-transactions/export.csv?from=invalid&to=2026-09-30')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['from']);
});

test('export validates date range', function () {
    actingAs($this->user)
        ->getJson('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-30&to=2026-09-01')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['to']);
});

test('tank level readings requires tank parameter', function () {
    actingAs($this->user)
        ->getJson('/api/v1/reports/tank-level-readings/export.csv?from=2026-09-01&to=2026-09-30')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['tank']);
});

test('unsupported report type returns 404', function () {
    actingAs($this->user)
        ->get('/api/v1/reports/invalid-report/export.csv?from=2026-09-01&to=2026-09-30')
        ->assertStatus(404);
});

test('export validates date range max 366 days', function () {
    actingAs($this->user)
        ->getJson('/api/v1/reports/main-tank-transactions/export.csv?from=2024-01-01&to=2025-01-05')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['to']);
});

test('tank level readings max 31 days', function () {
    actingAs($this->user)
        ->getJson('/api/v1/reports/tank-level-readings/export.csv?from=2026-08-01&to=2026-09-30&tank=1')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['to']);
});

test('CSV injection is prevented', function () {
    RfidTag::create([
        'tag_id' => '=HYPERLINK("http://evil.com","click me")',
        'status' => 'active',
    ]);

    Transaction::create([
        'transfer_type' => 'dispense_to_browser',
        'tank_id' => $this->browserTank->tank_id,
        'main_tank_id' => $this->mainTank->tank_id,
        'tag_id' => '=HYPERLINK("http://evil.com","click me")',
        'liters' => 100,
        'started_at' => '2026-09-10 10:00:00',
        'ended_at' => '2026-09-10 10:30:00',
        'sync_status' => 'live',
    ]);

    $response = actingAs($this->user)
        ->get('/api/v1/reports/browser-tank-refuels/export.csv?from=2026-09-01&to=2026-09-30')
        ->assertOk();

    $content = $response->streamedContent();
    expect($content)->toContain('\'=HYPERLINK');
});

test('Excel ID format uses semicolon and comma', function () {
    Transaction::create([
        'transfer_type' => 'vendor_fill',
        'tank_id' => $this->mainTank->tank_id,
        'main_tank_id' => $this->mainTank->tank_id,
        'liters' => 100.55,
        'started_at' => '2026-09-10 10:00:00',
        'ended_at' => '2026-09-10 10:30:00',
        'sync_status' => 'live',
    ]);

    $response = actingAs($this->user)
        ->get('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-01&to=2026-09-30&format=excel_id')
        ->assertOk();

    $content = $response->streamedContent();
    expect($content)->toContain('100,55');
    expect($content)->toContain(';');
});

test('export fails with 403 when user lacks permission', function () {
    $unauthorizedUser = User::factory()->create(['role_id' => null]);

    actingAs($unauthorizedUser)
        ->get('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-01&to=2026-09-30')
        ->assertStatus(403);
});

test('can export alarm log report', function () {
    AnomalyLog::create([
        'anomaly_type' => 'sudden_change_theft',
        'tank_id' => $this->mainTank->tank_id,
        'volume_diff' => -500.00,
        'anomaly_time' => '2026-09-10 02:00:00',
        'status_investigasi' => 'resolved',
        'resolution_note' => 'Sensor re-calibrated',
        'resolved_by' => $this->user->id,
        'resolved_at' => '2026-09-10 04:00:00',
    ]);

    $response = actingAs($this->user)
        ->get('/api/v1/reports/alarm-log/export.csv?from=2026-09-01&to=2026-09-30')
        ->assertOk();

    $content = $response->streamedContent();
    expect($content)->toContain('sudden_change_theft');
    expect($content)->toContain('Sensor re-calibrated');
});
