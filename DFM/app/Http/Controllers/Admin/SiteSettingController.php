<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingController extends Controller
{
    public function index(): Response
    {
        $site = Site::firstOrFail();

        return Inertia::render('admin/settings/index', [
            'settings' => $site->settings,
            'site' => $site,
        ]);
    }

    public function update(Request $request)
    {
        $site = Site::firstOrFail();

        $validated = $request->validate([
            'timezone' => 'required|string',
            'settings.settle_seconds' => 'required|integer',
            'settings.window_minutes' => 'required|integer',
            'settings.rfid_poll_seconds' => 'required|integer',
            'settings.eval_grace_minutes' => 'required|integer',
            'settings.reading_retention_days' => 'required|integer',
            'settings.position_max_age_minutes' => 'required|integer',
            'settings.geofence_exit_confirm_readings' => 'required|integer',
        ]);

        $site->timezone = $validated['timezone'];
        $site->settings = $validated['settings'];
        $site->settings_updated_by = $request->user()->id;
        $site->settings_updated_at = now();
        $site->save();

        return redirect()->back()->with('success', 'Site settings updated successfully.');
    }
}
