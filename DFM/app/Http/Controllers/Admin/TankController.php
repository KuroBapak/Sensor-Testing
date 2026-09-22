<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HardwareDevice;
use App\Models\Site;
use App\Models\Tank;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TankController extends Controller
{
    public function index(): Response
    {
        $tanks = Tank::orderBy('name')->get();
        $devices = HardwareDevice::with('tank')->orderBy('device_id')->get();

        return Inertia::render('admin/tanks/index', [
            'tanks' => $tanks,
            'devices' => $devices,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'division' => 'required|in:browser_tank,fuel_tanker,main_tank',
            'capacity_liters' => 'required|numeric|min:1',
            'length_cm' => 'nullable|numeric|min:1',
            'width_cm' => 'nullable|numeric|min:1',
            'height_cm' => 'nullable|numeric|min:1',
            'radius_cm' => 'nullable|numeric|min:1',
        ]);

        $site = Site::firstOrFail();
        $validated['site_id'] = $site->id;

        Tank::create($validated);

        return redirect()->back()->with('success', 'Tank created successfully.');
    }

    public function update(Request $request, Tank $tank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'division' => 'required|in:browser_tank,fuel_tanker,main_tank',
            'capacity_liters' => 'required|numeric|min:1',
            'length_cm' => 'nullable|numeric|min:1',
            'width_cm' => 'nullable|numeric|min:1',
            'height_cm' => 'nullable|numeric|min:1',
            'radius_cm' => 'nullable|numeric|min:1',
        ]);

        $tank->update($validated);

        return redirect()->back()->with('success', 'Tank updated successfully.');
    }

    public function destroy(Tank $tank)
    {
        $tank->delete();

        return redirect()->back()->with('success', 'Tank deleted successfully.');
    }
}
