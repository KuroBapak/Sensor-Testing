<?php

namespace App\Http\Controllers;

use App\Models\AnomalyLog;
use App\Models\Tank;
use App\Models\TankLevelReading;
use App\Models\Transaction;
use Inertia\Inertia;
use Inertia\Response;

class FuelMonitoringController extends Controller
{
    public function mainTank(): Response
    {
        // Get the main tank(s) with latest readings
        $mainTanks = Tank::where('division', 'main_tank')->pluck('tank_id');

        // Level readings for the chart (last 24h by default)
        $chartData = TankLevelReading::whereIn('tank_id', $mainTanks)
            ->where('timestamp', '>=', now()->subDay())
            ->orderBy('timestamp')
            ->get()
            ->map(fn ($r) => [
                'waktu' => $r->timestamp->format('H:i'),
                'total_liter' => (int) $r->level_liters,
                'liter_masuk' => 0,
                'liter_keluar' => 0,
            ]);

        // Transaction logs for the table
        $tableData = Transaction::whereIn('tank_id', $mainTanks)
            ->orderByDesc('started_at')
            ->limit(100)
            ->get()
            ->map(fn ($t) => [
                'waktu' => $t->started_at?->format('Y-m-d H:i:s'),
                'total_liter' => (int) $t->liters,
                'liter_masuk' => $t->transfer_type === 'vendor_fill' ? (int) $t->liters : 0,
                'liter_keluar' => $t->transfer_type !== 'vendor_fill' ? (int) $t->liters : 0,
            ]);

        return Inertia::render('fuel-monitoring/main-tank', [
            'initialChartData' => $chartData,
            'initialTableData' => $tableData,
        ]);
    }

    public function mobileTanks(): Response
    {
        // Fuel tanker transactions
        $fuelTankerTankIds = Tank::where('division', 'fuel_tanker')->pluck('tank_id');
        $fuelTankerLogs = Transaction::whereIn('tank_id', $fuelTankerTankIds)
            ->orderByDesc('started_at')
            ->limit(100)
            ->get()
            ->map(fn ($t) => [
                'tank_type' => 'fuel_tanker',
                'rfid' => $t->tag_id ?? '-',
                'waktu' => $t->started_at?->format('Y-m-d H:i'),
                'liter' => (int) $t->liters,
            ]);

        // Browser tank transactions
        $browserTankIds = Tank::where('division', 'browser_tank')->pluck('tank_id');
        $browserTankLogs = Transaction::whereIn('tank_id', $browserTankIds)
            ->orderByDesc('started_at')
            ->limit(100)
            ->get()
            ->map(fn ($t) => [
                'tank_type' => 'browser',
                'rfid' => $t->tag_id ?? '-',
                'waktu' => $t->started_at?->format('Y-m-d H:i'),
                'liter' => (int) $t->liters,
            ]);

        return Inertia::render('fuel-monitoring/mobile-tanks', [
            'initialFuelTankerData' => $fuelTankerLogs,
            'initialBrowserTankData' => $browserTankLogs,
        ]);
    }

    public function alarms(): Response
    {
        $userId = auth()->id();

        // Use real anomaly_logs with per-user read state
        $alarms = AnomalyLog::with(['tank', 'resolvedBy:id,name', 'reads' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->orderByDesc('anomaly_time')
            ->limit(200)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'rfid' => $a->tag_id ?? '-',
                'waktu_kejadian' => $a->anomaly_time?->format('Y-m-d H:i:s'),
                'jumlah_liter' => (int) abs($a->volume_diff ?? 0),
                'anomaly_type' => $a->anomaly_type,
                'tank_name' => $a->tank?->name,
                'status' => $a->status_investigasi,
                'resolved_by' => $a->resolvedBy?->name,
                'is_read' => $a->reads->isNotEmpty(),
            ]);

        return Inertia::render('fuel-monitoring/alarms', [
            'initialAlarmData' => $alarms,
        ]);
    }
}
