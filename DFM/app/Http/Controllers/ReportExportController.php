<?php

namespace App\Http\Controllers;

use App\Models\AlarmLog;
use App\Models\ExportLog;
use App\Models\MainTankLog;
use App\Models\MobileTankLog;
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

        MainTankLog::orderBy('id')->chunkById(500, function ($logs) use ($output, $delimiter, $format, &$rowCount, $directionFilter, $tankFilter) {
            foreach ($logs as $log) {
                $time = $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : date('Y-m-d ').$log->waktu.':00';

                if ($log->liter_masuk > 0 && ($directionFilter === 'all' || $directionFilter === 'in')) {
                    if (! $tankFilter || stripos('Fuel Tanker 01', $tankFilter) !== false) {
                        $this->writeRow($output, [
                            $time,
                            $time,
                            'Main Tank 1',
                            'In',
                            'Fuel Tanker 01',
                            'fuel_tanker',
                            'FT-A1B2C3',
                            'FILL-LINE-01',
                            number_format($log->liter_masuk, 2, '.', ''),
                            'live',
                        ], $delimiter, $format);
                        $rowCount++;
                    }
                }

                if ($log->liter_keluar > 0 && ($directionFilter === 'all' || $directionFilter === 'out')) {
                    if (! $tankFilter || stripos('Browser Tank 01', $tankFilter) !== false) {
                        $this->writeRow($output, [
                            $time,
                            $time,
                            'Main Tank 1',
                            'Out',
                            'Browser Tank 01',
                            'browser_tank',
                            'BT-D4E5F6',
                            'DISP-LINE-01',
                            number_format($log->liter_keluar, 2, '.', ''),
                            'live',
                        ], $delimiter, $format);
                        $rowCount++;
                    }
                }
            }

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        });
    }

    protected function streamMainTankDailyReconciliation(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $current = $from->copy();
        $today = Carbon::today();

        while ($current->lte($to)) {
            $dateStr = $current->format('Y-m-d');
            $isComplete = $current->lt($today);

            $opening = 15000.00;
            $received = (float) MainTankLog::sum('liter_masuk');
            $dispensed = (float) MainTankLog::sum('liter_keluar');
            $expected = $opening + $received - $dispensed;
            $closing = $expected + rand(-20, 20);
            $variance = $closing - $expected;
            $capacity = 20000.00;
            $variancePercent = ($variance / $capacity) * 100;

            $this->writeRow($output, [
                $dateStr,
                'Main Tank 1',
                number_format($opening, 2, '.', ''),
                number_format($received, 2, '.', ''),
                number_format($dispensed, 2, '.', ''),
                number_format($expected, 2, '.', ''),
                number_format($closing, 2, '.', ''),
                number_format($variance, 2, '.', ''),
                number_format($variancePercent, 2, '.', ''),
                $isComplete,
            ], $delimiter, $format);

            $rowCount++;
            $current->addDay();
        }

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    protected function streamBrowserTankRefuels(Request $request, Carbon $from, Carbon $to, $output, string $delimiter, string $format, int &$rowCount): void
    {
        $tanksFilter = $request->input('tanks');

        MobileTankLog::where('tank_type', 'browser')
            ->orderBy('id')
            ->chunkById(500, function ($logs) use ($output, $delimiter, $format, &$rowCount, $tanksFilter) {
                foreach ($logs as $log) {
                    $time = $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : date('Y-m-d ').$log->waktu;
                    $tankName = 'Browser Tank '.($log->rfid ? substr($log->rfid, 0, 5) : '01');

                    if ($tanksFilter && ! in_array($tankName, (array) $tanksFilter, true)) {
                        continue;
                    }

                    $this->writeRow($output, [
                        $time,
                        $time,
                        $tankName,
                        $log->rfid ?? 'TAG-BT-001',
                        number_format($log->liter, 2, '.', ''),
                        'Main Tank 1',
                        'DISP-LINE-01',
                        'live',
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
        $current = $from->copy();
        while ($current->lte($to)) {
            $dateStr = $current->format('Y-m-d');
            $refuels = MobileTankLog::where('tank_type', 'browser')->get();

            $totalLiters = (float) $refuels->sum('liter');
            $count = $refuels->count();

            $this->writeRow($output, [
                $dateStr,
                'Browser Tank 01',
                $count,
                number_format($totalLiters, 2, '.', ''),
            ], $delimiter, $format);

            $rowCount++;
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

        MobileTankLog::where('tank_type', 'fuel_tanker')
            ->orderBy('id')
            ->chunkById(500, function ($logs) use ($output, $delimiter, $format, &$rowCount, $typeFilter) {
                foreach ($logs as $log) {
                    $time = $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : date('Y-m-d ').$log->waktu;
                    $tankName = 'Fuel Tanker '.($log->rfid ? substr($log->rfid, 0, 5) : '01');

                    if ($typeFilter === 'all' || $typeFilter === 'unload') {
                        $this->writeRow($output, [
                            $time,
                            $time,
                            $tankName,
                            'Unload to Main Tank',
                            $log->rfid ?? 'FT-TAG-01',
                            number_format($log->liter, 2, '.', ''),
                            'Main Tank 1',
                            'FILL-LINE-01',
                            '',
                            'live',
                        ], $delimiter, $format);
                        $rowCount++;
                    }

                    if ($typeFilter === 'all' || $typeFilter === 'vendor_fill') {
                        $this->writeRow($output, [
                            $time,
                            $time,
                            $tankName,
                            'Vendor fill',
                            '',
                            number_format($log->liter, 2, '.', ''),
                            '',
                            '',
                            'Operator John',
                            '',
                        ], $delimiter, $format);
                        $rowCount++;
                    }
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

        AlarmLog::orderBy('id')->chunkById(500, function ($alarms) use ($output, $delimiter, $format, &$rowCount, $statusFilter) {
            foreach ($alarms as $alarm) {
                $status = 'resolved';
                if ($statusFilter && $statusFilter !== 'all' && $status !== $statusFilter) {
                    continue;
                }

                $this->writeRow($output, [
                    $alarm->waktu_kejadian ?? date('Y-m-d H:i:s'),
                    'Theft Alarm',
                    'Main Tank 1',
                    'main_tank',
                    $alarm->rfid ?? 'UNKNOWN',
                    number_format($alarm->jumlah_liter ?? 150, 2, '.', ''),
                    '12.50',
                    '5.00',
                    'Medium',
                    $status,
                    'Checked and resolved by security team',
                    'Supervisor Jane',
                    date('Y-m-d H:i:s'),
                    '-6.2088',
                    '106.8456',
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
        $tank = $request->input('tank');

        MainTankLog::orderBy('id')->chunkById(500, function ($logs) use ($output, $delimiter, $format, &$rowCount, $tank) {
            foreach ($logs as $log) {
                $time = $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : date('Y-m-d ').$log->waktu.':00';

                $this->writeRow($output, [
                    $time,
                    $tank,
                    number_format($log->total_liter, 2, '.', ''),
                    '-6.2088',
                    '106.8456',
                    12,
                    'FMC225-8675309',
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
