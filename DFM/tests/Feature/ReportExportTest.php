<?php

use App\Models\ExportLog;
use App\Models\MainTankLog;
use App\Models\MobileTankLog;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can export main tank transactions report', function () {
    MainTankLog::create([
        'waktu' => '2026-09-10 10:00:00',
        'total_liter' => 5000,
        'liter_masuk' => 100,
        'liter_keluar' => 0,
    ]);

    actingAs($this->user)
        ->get('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-01&to=2026-09-30&format=standard')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertHeader('Content-Disposition', 'attachment; filename="main-tank-transactions_2026-09-01_to_2026-09-30_UTC.csv"');
});

test('export creates audit log entry', function () {
    MainTankLog::create([
        'waktu' => '2026-09-10 10:00:00',
        'total_liter' => 5000,
        'liter_masuk' => 100,
        'liter_keluar' => 0,
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
        ->getJson('/api/v1/reports/tank-level-readings/export.csv?from=2026-08-01&to=2026-09-30&tank=Main%20Tank%201')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['to']);
});

test('CSV injection is prevented', function () {
    MobileTankLog::create([
        'tank_type' => 'browser',
        'waktu' => '2026-09-10 10:00:00',
        'rfid' => '=HYPERLINK("http://evil.com","click me")',
        'liter' => 100,
    ]);

    $response = actingAs($this->user)
        ->get('/api/v1/reports/browser-tank-refuels/export.csv?from=2026-09-01&to=2026-09-30')
        ->assertOk();

    $content = $response->streamedContent();
    expect($content)->toContain('\'=HYPERLINK');
});

test('Excel ID format uses semicolon and comma', function () {
    MainTankLog::create([
        'waktu' => '2026-09-10 10:00:00',
        'total_liter' => 5000,
        'liter_masuk' => 100.55,
        'liter_keluar' => 0,
    ]);

    $response = actingAs($this->user)
        ->get('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-01&to=2026-09-30&format=excel_id')
        ->assertOk();

    $content = $response->streamedContent();
    expect($content)->toContain('100,55');
    expect($content)->toContain(';');
});

test('export fails with 403 when user lacks permission', function () {
    Gate::define('export-reports', fn () => false);

    actingAs($this->user)
        ->get('/api/v1/reports/main-tank-transactions/export.csv?from=2026-09-01&to=2026-09-30')
        ->assertStatus(403);
});
