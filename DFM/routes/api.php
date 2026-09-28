<?php

use App\Http\Controllers\Api\AlarmApiController;
use App\Http\Controllers\Api\AvlIngestionController;
use App\Http\Controllers\Api\GeofenceApiController;
use App\Http\Controllers\Api\RfidSyncController;
use App\Http\Controllers\Api\SiteReadingController;
use App\Http\Controllers\Api\TransactionIngestionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Edge API Routes (Hardware IoT → Cloud)
|--------------------------------------------------------------------------
| These endpoints are called by the Teltonika RUT956 gateway over HTTPS.
| Auth: Bearer token matched against hardware_devices.api_token_hash.
*/

Route::prefix('v1')->middleware('device.token')->group(function () {
    // Transaction ingestion from RUT956
    Route::post('transactions', [TransactionIngestionController::class, 'store'])
        ->name('api.v1.transactions');

    // Site level readings from RUT956
    Route::post('site/readings', [SiteReadingController::class, 'store'])
        ->name('api.v1.site.readings');

    // RFID tag sync (304 if unchanged)
    Route::get('sync/rfid', [RfidSyncController::class, 'sync'])
        ->name('api.v1.sync.rfid');
});

/*
|--------------------------------------------------------------------------
| Internal API Routes (TCP Gateway → Laravel, localhost only)
|--------------------------------------------------------------------------
| AVL data ingestion from FMC225 via local TCP-to-HTTP bridge.
*/

Route::prefix('internal')->group(function () {
    Route::post('ingest/avl', [AvlIngestionController::class, 'store'])
        ->name('api.internal.ingest.avl');
});

/*
|--------------------------------------------------------------------------
| Geofence Admin API
|--------------------------------------------------------------------------
*/

Route::prefix('v1/admin')->middleware('auth:sanctum')->group(function () {
    Route::get('geofences', [GeofenceApiController::class, 'index'])
        ->name('api.v1.admin.geofences.index');
    Route::post('geofences', [GeofenceApiController::class, 'store'])
        ->name('api.v1.admin.geofences.store');
    Route::put('geofences/{geofence}', [GeofenceApiController::class, 'update'])
        ->name('api.v1.admin.geofences.update');
    Route::delete('geofences/{geofence}', [GeofenceApiController::class, 'destroy'])
        ->name('api.v1.admin.geofences.destroy');
});

/*
|--------------------------------------------------------------------------
| Alarm Management API
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::match(['post', 'patch'], 'alerts/{anomalyLog}/read', [AlarmApiController::class, 'markRead'])
        ->name('api.v1.alerts.read');
    Route::put('alerts/{anomalyLog}/status', [AlarmApiController::class, 'updateStatus'])
        ->name('api.v1.alerts.status');
});
