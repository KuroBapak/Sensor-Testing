<?php

namespace App\Http\Controllers;

use App\Models\AlarmLog;
use App\Models\MainTankLog;
use App\Models\MobileTankLog;
use Inertia\Inertia;

class FuelMonitoringController extends Controller
{
    public function mainTank()
    {
        // Get the latest logs
        $logs = MainTankLog::orderBy('id', 'asc')->get();

        return Inertia::render('fuel-monitoring/main-tank', [
            'initialChartData' => $logs,
            'initialTableData' => $logs,
        ]);
    }

    public function mobileTanks()
    {
        $fuelTankerLogs = MobileTankLog::where('tank_type', 'fuel_tanker')->orderBy('id', 'asc')->get();
        $browserTankLogs = MobileTankLog::where('tank_type', 'browser')->orderBy('id', 'asc')->get();

        return Inertia::render('fuel-monitoring/mobile-tanks', [
            'initialFuelTankerData' => $fuelTankerLogs,
            'initialBrowserTankData' => $browserTankLogs,
        ]);
    }

    public function alarms()
    {
        // Alarms typically shown newest first
        $alarms = AlarmLog::orderBy('id', 'desc')->get();

        return Inertia::render('fuel-monitoring/alarms', [
            'initialAlarmData' => $alarms,
        ]);
    }
}
