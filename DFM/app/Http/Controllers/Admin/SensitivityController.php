<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SensitivityController extends Controller
{
    public function index(): Response
    {
        $site = Site::firstOrFail();
        $settings = $site->settings ?? [];

        $defaultSensitivity = [
            'browser_tank_threshold' => 10,
            'fuel_tanker_threshold' => 10,
            'main_tank_threshold' => 2,
            'tolerance_pct' => 1.0,
        ];

        return Inertia::render('admin/sensitivity/index', [
            'sensitivity' => array_merge($defaultSensitivity, $settings['sensitivity'] ?? []),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'browser_tank_threshold' => 'required|numeric|min:0.1|max:100',
            'fuel_tanker_threshold' => 'required|numeric|min:0.1|max:100',
            'main_tank_threshold' => 'required|numeric|min:0.1|max:100',
            'tolerance_pct' => 'required|numeric|min:0.01|max:100',
        ]);

        $site = Site::firstOrFail();
        $settings = $site->settings ?? [];
        $settings['sensitivity'] = $validated;

        $site->settings = $settings;
        $site->settings_updated_by = $request->user()->id;
        $site->settings_updated_at = now();
        $site->save();

        return redirect()->back()->with('success', 'Sensitivity settings updated successfully.');
    }
}
