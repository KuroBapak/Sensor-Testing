<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RfidTag;
use App\Models\Site;
use App\Models\Tank;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RfidController extends Controller
{
    public function index(): Response
    {
        $tags = RfidTag::with('tank')->orderBy('tag_id')->get();
        $tanks = Tank::orderBy('name')->get(['tank_id', 'name', 'division']);

        return Inertia::render('admin/rfid/index', [
            'tags' => $tags,
            'tanks' => $tanks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_id' => 'required|string|max:255|unique:rfid_tags,tag_id',
            'tank_id' => 'nullable|exists:tanks,tank_id',
            'sector' => 'nullable|string|max:255',
            'status' => 'required|in:active,blocked',
        ]);

        RfidTag::create($validated);

        // Bump RFID list version so devices know to re-sync
        $site = Site::firstOrFail();
        $site->increment('rfid_list_version');

        return redirect()->back()->with('success', 'RFID tag registered.');
    }

    public function update(Request $request, RfidTag $rfidTag)
    {
        $validated = $request->validate([
            'tank_id' => 'nullable|exists:tanks,tank_id',
            'sector' => 'nullable|string|max:255',
            'status' => 'required|in:active,blocked',
        ]);

        $rfidTag->update($validated);

        $site = Site::firstOrFail();
        $site->increment('rfid_list_version');

        return redirect()->back()->with('success', 'RFID tag updated.');
    }

    public function destroy(RfidTag $rfidTag)
    {
        $rfidTag->delete();

        $site = Site::firstOrFail();
        $site->increment('rfid_list_version');

        return redirect()->back()->with('success', 'RFID tag deleted.');
    }
}
