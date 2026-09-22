<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SensitivityController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TankController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FuelMonitoring\MapController;
use App\Http\Controllers\FuelMonitoringController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // Fuel Monitoring Routes
    Route::get('fuel-monitoring/main-tank', [FuelMonitoringController::class, 'mainTank'])
        ->middleware('permission:main_tank.view')
        ->name('fuel-monitoring.main-tank');

    Route::get('fuel-monitoring/mobile-tanks', [FuelMonitoringController::class, 'mobileTanks'])
        ->middleware('permission:mobile_tanks.view')
        ->name('fuel-monitoring.mobile-tanks');

    Route::get('fuel-monitoring/alarms', [FuelMonitoringController::class, 'alarms'])
        ->middleware('permission:alarms.view')
        ->name('fuel-monitoring.alarms');

    // Reports Page
    Route::inertia('reports', 'reports/index')
        ->middleware('permission:reports.export')
        ->name('reports.index');

    // Fleet Map
    Route::get('fuel-monitoring/map', [MapController::class, 'index'])
        ->middleware('permission:map.view')
        ->name('fuel-monitoring.map');

    // Admin: Tanks & Hardware
    Route::get('admin/tanks', [TankController::class, 'index'])
        ->middleware('permission:tanks.manage')
        ->name('admin.tanks.index');
    Route::post('admin/tanks', [TankController::class, 'store'])
        ->middleware('permission:tanks.manage')
        ->name('admin.tanks.store');
    Route::put('admin/tanks/{tank}', [TankController::class, 'update'])
        ->middleware('permission:tanks.manage')
        ->name('admin.tanks.update');
    Route::delete('admin/tanks/{tank}', [TankController::class, 'destroy'])
        ->middleware('permission:tanks.manage')
        ->name('admin.tanks.destroy');

    // Admin: Sensitivity
    Route::get('admin/sensitivity', [SensitivityController::class, 'index'])
        ->middleware('permission:sensitivity.manage')
        ->name('admin.sensitivity.index');

    Route::post('admin/sensitivity', [SensitivityController::class, 'update'])
        ->middleware('permission:sensitivity.manage')
        ->name('admin.sensitivity.update');

    // Admin: Backup
    Route::get('admin/backup', [BackupController::class, 'index'])
        ->middleware('permission:backup.manage')
        ->name('admin.backup.index');

    Route::post('admin/backup', [BackupController::class, 'update'])
        ->middleware('permission:backup.manage')
        ->name('admin.backup.update');

    Route::post('admin/backup/trigger', [BackupController::class, 'trigger'])
        ->middleware('permission:backup.manage')
        ->name('admin.backup.trigger');

    // Admin: Settings
    Route::get('admin/settings', [SiteSettingController::class, 'index'])
        ->middleware('permission:settings.manage')
        ->name('admin.settings.index');

    Route::post('admin/settings', [SiteSettingController::class, 'update'])
        ->middleware('permission:settings.manage')
        ->name('admin.settings.update');

    // Admin: Role Management
    Route::prefix('admin/roles')->name('admin.roles.')->middleware('permission:roles.manage')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    // Admin: User Management
    Route::prefix('admin/users')->name('admin.users.')->middleware('permission:users.manage')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});

// API Routes (can be accessed with auth middleware or separately as needed)
Route::get('api/v1/reports/{reportType}/export.csv', [ReportExportController::class, 'export'])->name('api.reports.export');

require __DIR__.'/settings.php';
