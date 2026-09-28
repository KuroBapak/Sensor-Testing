<?php

namespace App\Http\Controllers;

use App\Models\AnomalyLog;
use App\Models\ExportLog;
use App\Models\Tank;
use App\Models\TankLevelReading;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    protected array $supportedReports = [
        'main-tank-transactions',
        'main-tank-daily-reconciliation',
        'browser-tank-refuels',
        'browser-tank-daily-consumption',
        'fuel-tanker-unloads',
        'alarm-log',
        'tank-level-readings',
    ];

    public function export(Request $request, string $reportType)
    {
        if (! in_array($reportType, $this->supportedReports, true)) {
            abort(404, 'Report type not supported.');
        }

        Gate::authorize('export-reports');
        Gate::authorize("export-{$reportType}");

        $rules = [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'format' => ['nullable', 'in:standard,excel_id'],
        ];

        if ($reportType === 'tank-level-readings') {
            $rules['tank'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        $from = Carbon::parse($validated['from'])->startOfDay();
        $to = Carbon::parse($validated['to'])->endOfDay();
        $daysDiff = $from->diffInDays($to);
        $maxDays = ($reportType === 'tank-level-readings') ? 31 : 366;

        if ($daysDiff > $maxDays) {
            throw ValidationException::withMessages([
                'to' => "Date range cannot exceed {$maxDays} days for this report.",
            ]);
        }

        return $this->streamResponse($request, $reportType, $validated, $from, $to);
    }

    protected function streamResponse(Request $request, string $reportType, array $validated, Carbon $from, Carbon $to): StreamedResponse
    {
        $format = $request->input('format', 'standard');
        $delimiter = $format === 'excel_id' ? ';' : ',';
        $timezone = config('app.timezone', 'Asia/Jakarta');

        $exportLog = ExportLog::create([
            'user_id' => $request->user()?->id,
            'report_type' => $reportType,
            'filters' => $request->all(),
            'format_preset' => $format,
            'row_count' => null,
        ]);

        $fileName = sprintf(
            '%s_%s_to_%s_%s.csv',
            $reportType,
            $validated['from'],
            $validated['to'],
            str_replace('/', '-', $timezone)
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ];

        return new StreamedResponse(function () use ($reportType, $request, $from, $to, $format, $delimiter, $exportLog) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write Headers
            $csvHeaders = $this->getHeaders($reportType);
            fputcsv($output, $csvHeaders, $delimiter);

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            $rowCount = 0;
            $this->streamReportData($reportType, $request, $from, $to, $output, $delimiter, $format, $rowCount);

            fclose($output);

            $exportLog->update(['row_count' => $rowCount]);
        }, 200, $headers);
    }

    protected function getHeaders(string $reportType): array
    {
        return match ($reportType) {
            'main-tank-transactions' => [
                'started_at', 'ended_at', 'main_tank', 'direction', 'tank', 'division', 'rfid_tag', 'line_device_id', 'liters', 'sync_status',
            ],
            'main-tank-daily-reconciliation' => [
                'date', 'main_tank', 'opening_level_l', 'received_l', 'dispensed_l', 'expected_closing_l', 'closing_level_l', 'variance_l', 'variance_percent', 'is_complete',
            ],
            'browser-tank-refuels' => [
                'started_at', 'ended_at', 'browser_tank', 'rfid_tag', 'liters', 'main_tank', 'line_device_id', 'sync_status',
            ],
            'browser-tank-daily-consumption' => [
                'date', 'browser_tank', 'refuel_count', 'liters_received',
            ],
            'fuel-tanker-unloads' => [
                'started_at', 'ended_at', 'fuel_tanker', 'type', 'rfid_tag', 'liters', 'main_tank', 'line_device_id', 'entered_by', 'sync_status',
            ],
            'alarm-log' => [
                'anomaly_time', 'type', 'tank', 'division', 'rfid_tag', 'volume_diff_l', 'observed_percent', 'threshold_percent', 'sensitivity_mode', 'status', 'resolution_note', 'resolved_by', 'resolved_at', 'latitude', 'longitude',
            ],
            'tank-level-readings' => [
                'timestamp', 'tank', 'level_l', 'latitude', 'longitude', 'satellites', 'device_id',
            ],
            default => [],
        };
    }

    protected function streamReportData(
        string $reportType,
        Request $request,
        Carbon $from,
        Carbon $to,
        $output,
        string $delimiter,
        string $format,
        int &$rowCount
    ): void {
        match ($reportType) {
            'main-tank-transactions' => $this->streamMainTankTransactions($request, $from, $to, $output, $delimiter, $format, $rowCount),
            'main-tank-daily-reconciliation' => $this->streamMainTankDailyReconciliation($request, $from, $to, $output, $delimiter, $format, $rowCount),
            'browser-tank-refuels' => $this->streamBrowserTankRefuels($request, $from, $to, $output, $delimiter, $format, $rowCount),
            'browser-tank-daily-consumption' => $this->streamBrowserTankDailyConsumption($request, $from, $to, $output, $delimiter, $format, $rowCount),
            'fuel-tanker-unloads' => $this->streamFuelTankerUnloads($request, $from, $to, $output, $delimiter, $format, $rowCount),
            'alarm-log' => $this->streamAlarmLog($request, $from, $to, $output, $delimiter, $format, $rowCount),
            'tank-level-readings' => $this->streamTankLevelReadings($request, $from, $to, $output, $delimiter, $format, $rowCount),
        };
    }

    protected function streamMainTankTransactions(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $directionFilter = strtolower($request->input('direction', 'all'));
        $tankFilter = $request->input('tank');
        $mainTankIds = Tank::where('division', 'main_tank')->pluck('tank_id');

        $query = Transaction::with(['tank', 'mainTank', 'device'])
            ->where(function ($q) use ($mainTankIds) {
                $q->whereIn('tank_id', $mainTankIds)
                    ->orWhereIn('main_tank_id', $mainTankIds);
            })
            ->whereBetween('started_at', [$from, $to])
            ->orderBy('id');

        $query->chunkById(500, function ($transactions) use ($output, $delimiter, $format, &$rowCount, $directionFilter, $tankFilter) {
            foreach ($transactions as $txn) {
                $direction = $txn->transfer_type === 'vendor_fill' ? 'In' : 'Out';
                if ($directionFilter !== 'all' && strtolower($direction) !== $directionFilter) {
                    continue;
                }

                $tankName = $txn->tank?->name ?? '';
                if ($tankFilter && stripos($tankName, $tankFilter) === false) {
                    continue;
                }

                $this->writeRow($output, [
                    $txn->started_at?->format('Y-m-d H:i:s'),
                    $txn->ended_at?->format('Y-m-d H:i:s'),
                    $txn->mainTank?->name ?? $txn->tank?->name,
                    $direction,
                    $txn->tank?->name,
                    $txn->tank?->division,
                    $txn->tag_id ?? '',
                    $txn->device_id ?? '',
                    number_format((float) $txn->liters, 2, '.', ''),
                    $txn->sync_status ?? 'live',
                ], $delimiter, $format);
                $rowCount++;
            }

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        });
    }

    protected function streamMainTankDailyReconciliation(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $mainTanks = Tank::where('division', 'main_tank')->get();
        $current = $from->copy();
        $today = Carbon::today();

        foreach ($mainTanks as $tank) {
            $loopDate = $current->copy();

            while ($loopDate->lte($to)) {
                $dateStr = $loopDate->format('Y-m-d');
                $dayStart = $loopDate->copy()->startOfDay();
                $dayEnd = $loopDate->copy()->endOfDay();
                $isComplete = $loopDate->lt($today);

                // Opening level: last reading before day start
                $openingReading = TankLevelReading::where('tank_id', $tank->tank_id)
                    ->where('timestamp', '<', $dayStart)
                    ->orderByDesc('timestamp')
                    ->first();
                $opening = $openingReading ? (float) $openingReading->level_liters : 0;

                // Received = sum of vendor_fill transactions for this tank on this day
                $received = (float) Transaction::where('tank_id', $tank->tank_id)
                    ->where('transfer_type', 'vendor_fill')
                    ->whereBetween('started_at', [$dayStart, $dayEnd])
                    ->sum('liters');

                // Dispensed = sum of outbound transactions (dispense_to_browser, fill_to_main) from this tank
                $dispensed = (float) Transaction::where('main_tank_id', $tank->tank_id)
                    ->whereIn('transfer_type', ['fill_to_main', 'dispense_to_browser'])
                    ->whereBetween('started_at', [$dayStart, $dayEnd])
                    ->sum('liters');

                $expected = $opening + $received - $dispensed;

                // Closing level: last reading of the day
                $closingReading = TankLevelReading::where('tank_id', $tank->tank_id)
                    ->whereBetween('timestamp', [$dayStart, $dayEnd])
                    ->orderByDesc('timestamp')
                    ->first();
                $closing = $closingReading ? (float) $closingReading->level_liters : $expected;

                $variance = $closing - $expected;
                $capacity = (float) $tank->capacity_liters ?: 1;
                $variancePercent = ($variance / $capacity) * 100;

                $this->writeRow($output, [
                    $dateStr,
                    $tank->name,
                    number_format($opening, 2, '.', ''),
                    number_format($received, 2, '.', ''),
                    number_format($dispensed, 2, '.', ''),
                    number_format($expected, 2, '.', ''),
                    number_format($closing, 2, '.', ''),
                    number_format($variance, 2, '.', ''),
                    number_format($variancePercent, 2, '.', ''),
                    $isComplete ? 'true' : 'false',
                ], $delimiter, $format);

                $rowCount++;
                $loopDate->addDay();
            }
        }

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    protected function streamBrowserTankRefuels(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $tanksFilter = $request->input('tanks');
        $browserTankIds = Tank::where('division', 'browser_tank')->pluck('tank_id');

        $query = Transaction::with(['tank', 'mainTank'])
            ->whereIn('tank_id', $browserTankIds)
            ->whereBetween('started_at', [$from, $to])
            ->orderBy('id');

        $query->chunkById(500, function ($transactions) use ($output, $delimiter, $format, &$rowCount, $tanksFilter) {
            foreach ($transactions as $txn) {
                if ($tanksFilter) {
                    $filterList = array_map('trim', explode(',', $tanksFilter));
                    $match = false;
                    foreach ($filterList as $f) {
                        if (stripos($txn->tank?->name ?? '', $f) !== false) {
                            $match = true;

                            break;
                        }
                    }
                    if (! $match) {
                        continue;
                    }
                }

                $this->writeRow($output, [
                    $txn->started_at?->format('Y-m-d H:i:s'),
                    $txn->ended_at?->format('Y-m-d H:i:s'),
                    $txn->tank?->name,
                    $txn->tag_id ?? '',
                    number_format((float) $txn->liters, 2, '.', ''),
                    $txn->mainTank?->name ?? '',
                    $txn->device_id ?? '',
                    $txn->sync_status ?? 'live',
                ], $delimiter, $format);
                $rowCount++;
            }

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        });
    }

    protected function streamBrowserTankDailyConsumption(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $browserTanks = Tank::where('division', 'browser_tank')->get();
        $current = $from->copy();

        while ($current->lte($to)) {
            $dateStr = $current->format('Y-m-d');
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();

            foreach ($browserTanks as $tank) {
                $totalLiters = (float) Transaction::where('tank_id', $tank->tank_id)
                    ->whereBetween('started_at', [$dayStart, $dayEnd])
                    ->sum('liters');

                $count = Transaction::where('tank_id', $tank->tank_id)
                    ->whereBetween('started_at', [$dayStart, $dayEnd])
                    ->count();

                $this->writeRow($output, [
                    $dateStr,
                    $tank->name,
                    $count,
                    number_format($totalLiters, 2, '.', ''),
                ], $delimiter, $format);
                $rowCount++;
            }

            $current->addDay();
        }

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    protected function streamFuelTankerUnloads(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $typeFilter = $request->input('type', 'all');
        $fuelTankerIds = Tank::where('division', 'fuel_tanker')->pluck('tank_id');

        $query = Transaction::with(['tank', 'mainTank'])
            ->where(function ($q) use ($fuelTankerIds) {
                $q->whereIn('tank_id', $fuelTankerIds)
                    ->orWhereIn('main_tank_id', $fuelTankerIds);
            })
            ->whereBetween('started_at', [$from, $to])
            ->orderBy('id');

        $query->chunkById(500, function ($transactions) use ($output, $delimiter, $format, &$rowCount, $typeFilter) {
            foreach ($transactions as $txn) {
                $txnType = $txn->transfer_type === 'vendor_fill' ? 'Vendor fill' : 'Unload to Main Tank';

                if ($typeFilter !== 'all') {
                    if ($typeFilter === 'vendor_fill' && $txn->transfer_type !== 'vendor_fill') {
                        continue;
                    }
                    if ($typeFilter === 'unload' && $txn->transfer_type === 'vendor_fill') {
                        continue;
                    }
                }

                $this->writeRow($output, [
                    $txn->started_at?->format('Y-m-d H:i:s'),
                    $txn->ended_at?->format('Y-m-d H:i:s'),
                    $txn->tank?->name,
                    $txnType,
                    $txn->tag_id ?? '',
                    number_format((float) $txn->liters, 2, '.', ''),
                    $txn->mainTank?->name ?? '',
                    $txn->device_id ?? '',
                    $txn->enteredBy?->name ?? '',
                    $txn->sync_status ?? 'live',
                ], $delimiter, $format);
                $rowCount++;
            }

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        });
    }

    protected function streamAlarmLog(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $statusFilter = $request->input('status');

        AnomalyLog::with(['tank', 'resolvedBy'])
            ->whereBetween('anomaly_time', [$from, $to])
            ->orderBy('id')
            ->chunkById(500, function ($anomalies) use ($output, $delimiter, $format, &$rowCount, $statusFilter) {
                foreach ($anomalies as $anomaly) {
                    if ($statusFilter && $statusFilter !== 'all' && $anomaly->status_investigasi !== $statusFilter) {
                        continue;
                    }

                    $this->writeRow($output, [
                        $anomaly->anomaly_time?->format('Y-m-d H:i:s'),
                        $anomaly->anomaly_type,
                        $anomaly->tank?->name ?? '',
                        $anomaly->tank?->division ?? '',
                        $anomaly->tag_id ?? '',
                        number_format(abs((float) $anomaly->volume_diff), 2, '.', ''),
                        number_format((float) $anomaly->observed_percent, 2, '.', ''),
                        number_format((float) $anomaly->threshold_percent, 2, '.', ''),
                        $anomaly->sensitivity_mode ?? '',
                        $anomaly->status_investigasi,
                        $anomaly->resolution_note ?? '',
                        $anomaly->resolvedBy?->name ?? '',
                        $anomaly->resolved_at?->format('Y-m-d H:i:s') ?? '',
                        (string) ($anomaly->latitude ?? ''),
                        (string) ($anomaly->longitude ?? ''),
                    ], $delimiter, $format);
                    $rowCount++;
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            });
    }

    protected function streamTankLevelReadings(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $tankFilter = $request->input('tank');

        $query = TankLevelReading::with(['tank', 'device'])
            ->whereBetween('timestamp', [$from, $to])
            ->orderBy('id');

        if ($tankFilter) {
            $query->whereHas('tank', fn ($q) => $q->where('name', 'like', "%{$tankFilter}%"));
        }

        $query->chunkById(500, function ($readings) use ($output, $delimiter, $format, &$rowCount) {
            foreach ($readings as $reading) {
                $this->writeRow($output, [
                    $reading->timestamp?->format('Y-m-d H:i:s'),
                    $reading->tank?->name ?? '',
                    number_format((float) $reading->level_liters, 2, '.', ''),
                    (string) ($reading->latitude ?? ''),
                    (string) ($reading->longitude ?? ''),
                    (string) ($reading->satellites ?? ''),
                    $reading->device_id ?? '',
                ], $delimiter, $format);
                $rowCount++;
            }

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        });
    }

    protected function writeRow($output, array $row, string $delimiter, string $format): void
    {
        $escapedRow = array_map(fn ($cell) => $this->escapeCsvCell($cell, $format), $row);
        fputcsv($output, $escapedRow, $delimiter);
    }

    protected function escapeCsvCell(mixed $value, string $format = 'standard'): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            if ($format === 'excel_id') {
                return str_replace('.', ',', (string) $value);
            }

            return (string) $value;
        }

        $str = (string) $value;

        if (preg_match('/^[=+\-@\t\r]/', $str)) {
            return "'".$str;
        }

        return $str;
    }
}
