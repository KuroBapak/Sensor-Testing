<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunBackupJob;
use App\Models\BackupRun;
use App\Models\BackupSetting;
use App\Models\Site;
use App\Services\BackupStorageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BackupController extends Controller
{
    public function index(): Response
    {
        $site = Site::firstOrFail();
        $backupSettings = BackupSetting::where('site_id', $site->id)->first();

        $runs = BackupRun::where('site_id', $site->id)
            ->orderBy('started_at', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('admin/backup/index', [
            'backupSettings' => $backupSettings,
            'runs' => $runs,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'endpoint' => 'required|url',
            'region' => 'required|string',
            'bucket' => 'required|string',
            'path_prefix' => 'nullable|string',
            'use_path_style' => 'boolean',
            'access_key_id' => 'required|string',
            'secret_access_key' => 'required|string',
            'schedule_time' => 'required|date_format:H:i',
            'retention_days' => 'required|integer|min:1',
            'enabled' => 'boolean',
        ]);

        $site = Site::firstOrFail();

        $setting = BackupSetting::firstOrNew(['site_id' => $site->id]);

        // Check if critical fields changed
        $criticalFields = ['endpoint', 'region', 'bucket', 'path_prefix', 'access_key_id', 'secret_access_key'];
        $settingsChanged = false;

        foreach ($criticalFields as $field) {
            if ($setting->exists && $setting->{$field} !== $validated[$field]) {
                $settingsChanged = true;
                break;
            }
        }

        $setting->fill($validated);
        $setting->updated_by = $request->user()->id;

        // Reset test status if settings changed
        if ($settingsChanged || ! $setting->exists) {
            $setting->resetTestStatus();
        }

        $setting->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Backup settings saved. Please run Test Connection before enabling backups.']);
        return redirect()->back()->with('success', 'Backup settings saved. Please run Test Connection before enabling backups.');
    }

    public function testConnection(Request $request, BackupStorageService $backupService)
    {
        $site = Site::firstOrFail();
        $settings = BackupSetting::where('site_id', $site->id)->firstOrFail();

        $result = $backupService->testConnection($settings);

        $settings->update([
            'last_connection_test_at' => now(),
            'last_connection_test_ok' => $result['success'],
        ]);

            Inertia::flash('toast', ['type' => 'success', 'message' => $result['message']]);
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        Inertia::flash('toast', ['type' => 'error', 'message' => $result['message']]);
        return redirect()->back()->withErrors(['test_connection' => $result['message']]);
    }

    public function testBackup(Request $request)
    {
        $site = Site::firstOrFail();
        $settings = BackupSetting::where('site_id', $site->id)->firstOrFail();

        // Validate connection test passed
        if (! $settings->last_connection_test_ok || ! $settings->last_connection_test_at) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Please run Test Connection first.']);
            return redirect()->back()->withErrors(['test_backup' => 'Please run Test Connection first.']);
        }

        // Check if settings changed after connection test
        if ($settings->updated_at > $settings->last_connection_test_at) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Settings changed after connection test. Please run Test Connection again.']);
            return redirect()->back()->withErrors(['test_backup' => 'Settings changed after connection test. Please run Test Connection again.']);
        }

        $run = BackupRun::create([
            'site_id' => $site->id,
            'trigger' => 'test',
            'status' => 'running',
            'started_at' => now(),
            'triggered_by' => $request->user()->id,
        ]);

        RunBackupJob::dispatchSync($run);
        $run->refresh();

        if ($run->status === 'success') {
            $size = $run->size_bytes ? number_format($run->size_bytes / 1024, 1) . ' KB' : '';
            Inertia::flash('toast', ['type' => 'success', 'message' => "Test backup completed successfully ($size)."]);
            return redirect()->back()->with('success', "Test backup completed successfully ($size).");
        }

        Inertia::flash('toast', ['type' => 'error', 'message' => 'Test backup failed: ' . ($run->error_message ?? 'Unknown error')]);
        return redirect()->back()->withErrors(['test_backup' => 'Test backup failed: ' . ($run->error_message ?? 'Unknown error')]);
    }

    public function trigger(Request $request)
    {
        $site = Site::firstOrFail();
        $settings = BackupSetting::where('site_id', $site->id)->first();

        if (! $settings) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Backup settings not configured.']);
            return redirect()->back()->withErrors(['trigger' => 'Backup settings not configured.']);
        }

        // Validate tests passed before manual trigger
        if (! $settings->isFullyTested()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Please complete Test Connection and Test Backup before running manual backups.']);
            return redirect()->back()->withErrors(['trigger' => 'Please complete Test Connection and Test Backup before running manual backups.']);
        }

        $run = BackupRun::create([
            'site_id' => $site->id,
            'trigger' => 'manual',
            'status' => 'running',
            'started_at' => now(),
            'triggered_by' => $request->user()->id,
        ]);

        RunBackupJob::dispatch($run);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Backup triggered successfully. It will run in the background.']);
        return redirect()->back()->with('success', 'Backup triggered successfully. It will run in the background.');
    }
}
