<?php

namespace App\Http\Controllers;

use App\Models\AnomalyLog;
use App\Models\Site;
use App\Models\Tank;
use App\Models\TankLevelReading;
use App\Models\Transaction;
use Inertia\Inertia;
use Inertia\Response;

class FuelMonitoringController extends Controller
{
    public function mainTank(): Response
    {
        // Get the main tank(s)
        $mainTanks = Tank::where('division', 'main_tank')->pluck('tank_id');

        // Current level from the latest ATG reading
        $currentReading = TankLevelReading::whereIn('tank_id', $mainTanks)
            ->orderByDesc('timestamp')
            ->first();

        // Hourly aggregated transactions for the last 24h (PRD §6 Page 1)
        // Groups by hour and calculates liter_masuk (fill_to_main) vs liter_keluar (dispense_to_browser)
        $site = Site::first();
        $timezone = $site->timezone ?? 'UTC';

        $transactions = Transaction::where(function ($query) use ($mainTanks) {
            $query->whereIn('main_tank_id', $mainTanks)
                ->orWhereIn('tank_id', $mainTanks);
        })
            ->where('started_at', '>=', now($timezone)->subDay())
            ->orderBy('started_at')
            ->get();

        // Bucket by hour in the site timezone
        $hourlyBuckets = [];
        for ($i = 23; $i >= 0; $i--) {
            $hourTime = now($timezone)->subHours($i);
            $hourKey = $hourTime->format('H:00');
            $hourlyBuckets[$hourKey] = [
                'waktu' => $hourKey,
                'total_liter' => (int) ($currentReading?->level_liters ?? 0),
                'liter_masuk' => 0,
                'liter_keluar' => 0,
            ];
        }

        foreach ($transactions as $txn) {
            $hourKey = $txn->started_at->setTimezone($timezone)->format('H:00');
            if (isset($hourlyBuckets[$hourKey])) {
                if ($txn->transfer_type === 'fill_to_main') {
                    $hourlyBuckets[$hourKey]['liter_masuk'] += (int) $txn->liters;
                } elseif ($txn->transfer_type === 'dispense_to_browser') {
                    $hourlyBuckets[$hourKey]['liter_keluar'] += (int) $txn->liters;
                }
            }
        }

        $chartData = array_values($hourlyBuckets);

        // Transaction logs for the table (PRD §6: newest rows first, scrollable)
        $tableData = Transaction::where(function ($query) use ($mainTanks) {
            $query->whereIn('main_tank_id', $mainTanks)
                ->orWhereIn('tank_id', $mainTanks);
        })
            ->orderByDesc('started_at')
            ->limit(100)
            ->get()
            ->map(fn ($t) => [
                'waktu' => $t->started_at?->setTimezone($timezone)->format('Y-m-d H:i:s'),
                'total_liter' => (int) $t->liters,
                'liter_masuk' => $t->transfer_type === 'fill_to_main' ? (int) $t->liters : 0,
                'liter_keluar' => $t->transfer_type === 'dispense_to_browser' ? (int) $t->liters : 0,
            ]);

        return Inertia::render('fuel-monitoring/main-tank', [
            'initialChartData' => $chartData,
            'initialTableData' => $tableData,
            'currentLevel' => (int) ($currentReading?->level_liters ?? 0),
        ]);
    }

    public function mobileTanks(): Response
    {
        $site = Site::first();
        $timezone = $site->timezone ?? 'UTC';

        // Get all registered Browser Tanks with their latest ATG readings (PRD §6 Page 2)
        $browserTanks = Tank::where('division', 'browser_tank')
            ->with(['latestReading'])
            ->get()
            ->map(function ($tank) use ($timezone) {
                // Level chart data - last 24h of ATG readings
                $levelReadings = TankLevelReading::where('tank_id', $tank->tank_id)
                    ->where('timestamp', '>=', now($timezone)->subDay())
                    ->orderBy('timestamp')
                    ->get()
                    ->map(fn ($r) => [
                        'waktu' => $r->timestamp->setTimezone($timezone)->format('H:i'),
                        'liter' => (int) $r->level_liters,
                    ]);

                // Transaction history for this tank
                $transactions = Transaction::where('tank_id', $tank->tank_id)
                    ->orderByDesc('started_at')
                    ->limit(50)
                    ->get()
                    ->map(fn ($t) => [
                        'tank_type' => 'browser',
                        'rfid' => $t->tag_id ?? '-',
                        'waktu' => $t->started_at?->setTimezone($timezone)->format('Y-m-d H:i'),
                        'liter' => (int) $t->liters,
                    ]);

                return [
                    'tank_id' => $tank->tank_id,
                    'name' => $tank->name,
                    'capacity' => $tank->capacity_liters,
                    'current_level' => (int) ($tank->latestReading?->level_liters ?? 0),
                    'level_chart_data' => $levelReadings,
                    'transactions' => $transactions,
                ];
            });

        // Get all registered Fuel Tankers with their latest ATG readings (PRD §6 Page 2)
        $fuelTankers = Tank::where('division', 'fuel_tanker')
            ->with(['latestReading'])
            ->get()
            ->map(function ($tank) use ($timezone) {
                // Level chart data - last 24h of ATG readings
                $levelReadings = TankLevelReading::where('tank_id', $tank->tank_id)
                    ->where('timestamp', '>=', now($timezone)->subDay())
                    ->orderBy('timestamp')
                    ->get()
                    ->map(fn ($r) => [
                        'waktu' => $r->timestamp->setTimezone($timezone)->format('H:i'),
                        'liter' => (int) $r->level_liters,
                    ]);

                // Transaction history for this tank
                $transactions = Transaction::where('tank_id', $tank->tank_id)
                    ->orderByDesc('started_at')
                    ->limit(50)
                    ->get()
                    ->map(fn ($t) => [
                        'tank_type' => 'fuel_tanker',
                        'rfid' => $t->tag_id ?? '-',
                        'waktu' => $t->started_at?->setTimezone($timezone)->format('Y-m-d H:i'),
                        'liter' => (int) $t->liters,
                    ]);

                return [
                    'tank_id' => $tank->tank_id,
                    'name' => $tank->name,
                    'capacity' => $tank->capacity_liters,
                    'current_level' => (int) ($tank->latestReading?->level_liters ?? 0),
                    'level_chart_data' => $levelReadings,
                    'transactions' => $transactions,
                ];
            });

        return Inertia::render('fuel-monitoring/mobile-tanks', [
            'browserTanks' => $browserTanks,
            'fuelTankers' => $fuelTankers,
        ]);
    }

    public function alarms(): Response
    {
        $userId = auth()->id();
        $site = Site::first();
        $timezone = $site->timezone ?? 'UTC';

        // Use real anomaly_logs with per-user read state (PRD §6 Page 3)
        $alarms = AnomalyLog::with(['tank', 'resolvedBy:id,name', 'reads' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->orderByDesc('anomaly_time')
            ->limit(200)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'rfid' => $a->tag_id ?? '-',
                'waktu_kejadian' => $a->anomaly_time?->setTimezone($timezone)->format('Y-m-d H:i:s'),
                'jumlah_liter' => (int) abs($a->volume_diff ?? 0),
                'anomaly_type' => $a->anomaly_type,
                'tank_name' => $a->tank?->name,
                'status' => $a->status_investigasi,
                'resolved_by' => $a->resolvedBy?->name,
                'is_read' => $a->reads->isNotEmpty(),
                'latitude' => $a->latitude,
                'longitude' => $a->longitude,
                'observed_percent' => $a->observed_percent,
                'threshold_percent' => $a->threshold_percent,
                'sensitivity_mode' => $a->sensitivity_mode,
            ]);

        return Inertia::render('fuel-monitoring/alarms', [
            'initialAlarmData' => $alarms,
        ]);
    }
}
