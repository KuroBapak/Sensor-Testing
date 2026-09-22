<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunBackupJob;
use App\Models\BackupRun;
use App\Models\BackupSetting;
use App\Models\Site;
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
        $setting->fill($validated);
        $setting->updated_by = $request->user()->id;
        $setting->save();

        return redirect()->back()->with('success', 'Backup settings updated successfully.');
    }

    public function trigger(Request $request)
    {
        $site = Site::firstOrFail();

        $run = BackupRun::create([
            'site_id' => $site->id,
            'trigger' => 'manual',
            'status' => 'running', // Job will start as running
            'started_at' => now(),
            'triggered_by' => $request->user()->id,
        ]);

        RunBackupJob::dispatch($run);

        return redirect()->back()->with('success', 'Backup triggered successfully. It will run in the background.');
    }
}
