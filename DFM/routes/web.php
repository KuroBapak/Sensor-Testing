<?php

use App\Http\Controllers\FuelMonitoringController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // Fuel Monitoring Routes
    Route::get('fuel-monitoring/main-tank', [FuelMonitoringController::class, 'mainTank'])->name('fuel-monitoring.main-tank');
    Route::get('fuel-monitoring/mobile-tanks', [FuelMonitoringController::class, 'mobileTanks'])->name('fuel-monitoring.mobile-tanks');
    Route::get('fuel-monitoring/alarms', [FuelMonitoringController::class, 'alarms'])->name('fuel-monitoring.alarms');

    // Reports Page
    Route::inertia('reports', 'reports/index')->name('reports.index');
});

// API Routes (can be accessed with auth middleware or separately as needed)
Route::get('api/v1/reports/{reportType}/export.csv', [ReportExportController::class, 'export'])->name('api.reports.export');

require __DIR__.'/settings.php';
