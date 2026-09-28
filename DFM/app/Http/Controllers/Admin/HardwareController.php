<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HardwareDevice;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HardwareController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:255|unique:hardware_devices,device_id',
            'device_type' => 'required|in:main_tank_atg,fill_line,dispense_line,mobile_unit',
            'tank_id' => 'nullable|exists:tanks,tank_id',
            'status' => 'required|in:spare,active,retired',
        ]);

        $site = Site::firstOrFail();

        // Generate an API token and hash it
        $token = Str::random(40);

        HardwareDevice::create([
            'device_id' => $validated['device_id'],
            'device_type' => $validated['device_type'],
            'tank_id' => $validated['tank_id'],
            'status' => $validated['status'],
            'site_id' => $site->id,
            'api_token_hash' => hash('sha256', $token),
        ]);

        return redirect()->back()->with('success', 'Hardware device registered. Ensure you copy the API token for the device: '.$token);
    }

    public function update(Request $request, HardwareDevice $hardwareDevice)
    {
        $validated = $request->validate([
            'device_type' => 'required|in:main_tank_atg,fill_line,dispense_line,mobile_unit',
            'tank_id' => 'nullable|exists:tanks,tank_id',
            'status' => 'required|in:spare,active,retired',
        ]);

        $hardwareDevice->update($validated);

        return redirect()->back()->with('success', 'Hardware device updated.');
    }

    public function destroy(HardwareDevice $hardwareDevice)
    {
        $hardwareDevice->delete();

        return redirect()->back()->with('success', 'Hardware device deleted.');
    }

    public function regenerateToken(HardwareDevice $hardwareDevice)
    {
        $token = Str::random(40);
        $hardwareDevice->update([
            'api_token_hash' => hash('sha256', $token),
        ]);

        return redirect()->back()->with('success', 'Token regenerated. New token: '.$token);
    }
}
