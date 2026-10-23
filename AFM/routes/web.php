<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\BatchAddController;
use App\Http\Controllers\BatchAddExampleController;
use App\Http\Controllers\FullExportController;
use App\Http\Controllers\GlobalSettingsController;
use App\Http\Controllers\QrCardController;
use App\Http\Controllers\QrCardExportController;
use App\Http\Controllers\QrCardTopUpController;
use App\Http\Controllers\SiteLogoController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\VehicleTypeExportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tank')->name('home');

// The uploaded logo is public on purpose: the login page shows it too.
Route::get('site-logo', SiteLogoController::class)->name('site-logo');

Route::middleware('auth')->group(function () {
    Route::redirect('dashboard', '/tank')->name('dashboard');

    Route::get('tank', [TankController::class, 'index'])->name('tank.index');

    Route::get('usage', [UsageController::class, 'index'])->name('usage.index');
    Route::get('usage/cards/{card}', [UsageController::class, 'show'])->withTrashed()->name('usage.show');

    Route::get('vehicles/export', VehicleTypeExportController::class)->name('vehicles.export');
    Route::resource('vehicles', VehicleTypeController::class)
        ->parameters(['vehicles' => 'vehicleType'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('qr-cards/export', QrCardExportController::class)->name('qr-cards.export');
    Route::post('qr-cards/{qrCard}/top-ups', [QrCardTopUpController::class, 'store'])->name('qr-cards.top-ups.store');
    Route::resource('qr-cards', QrCardController::class)
        ->parameters(['qr-cards' => 'qrCard'])
        ->only(['index', 'store', 'destroy']);

    // One Batch Add for vehicle types and QR cards together: the cards depend on the types.
    Route::get('batch-add/example', BatchAddExampleController::class)->name('batch-add.example');
    Route::post('batch-add', [BatchAddController::class, 'store'])->name('batch-add.store');

    Route::get('global-settings', [GlobalSettingsController::class, 'edit'])->name('global-settings.edit');
    Route::put('global-settings', [GlobalSettingsController::class, 'update'])->name('global-settings.update');

    Route::get('export/full', FullExportController::class)->name('export.full');

    // Super Admin only: AdminAccountRequest and UserPolicy answer 403 for an Admin.
    Route::resource('admin-accounts', AdminAccountController::class)
        ->parameters(['admin-accounts' => 'user'])
        ->only(['index', 'store', 'update', 'destroy']);
    Route::put('admin-accounts/{user}/password', [AdminAccountController::class, 'resetPassword'])->name('admin-accounts.password');
});

require __DIR__.'/settings.php';
