<?php

namespace App\Http\Controllers;

use App\Exports\ExportContext;
use App\Exports\FullExport;
use App\Services\ActivityLogger;
use App\Services\SettingsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** "Export Semua Data": one workbook with every tab, for the chosen period. */
class FullExportController extends Controller
{
    public function __invoke(Request $request, SettingsService $settings, ActivityLogger $logger): BinaryFileResponse
    {
        $timezone = $settings->timezone();
        $today = CarbonImmutable::now($timezone);

        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = $data['from'] ?? $today->startOfMonth()->toDateString();
        $to = $data['to'] ?? $today->toDateString();

        $context = ExportContext::forDates($from, $to, $request->user()->name, $timezone, $settings->get('site_name'));

        $logger->log('export.full', null, null, ['from' => $from, 'to' => $to]);

        return Excel::download(app()->make(FullExport::class, ['context' => $context]), 'fuel-export-'.$today->format('Ymd-Hi').'.xlsx');
    }
}
