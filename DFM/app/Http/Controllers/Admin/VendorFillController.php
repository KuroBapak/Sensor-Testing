<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tank;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorFillController extends Controller
{
    public function index(): Response
    {
        $vendorFills = Transaction::where('transfer_type', 'vendor_fill')
            ->with('enteredBy')
            ->orderByDesc('started_at')
            ->paginate(20);

        $mainTanks = Tank::where('division', 'main_tank')->orderBy('name')->get(['tank_id', 'name']);

        return Inertia::render('admin/vendor-fills/index', [
            'vendorFills' => $vendorFills,
            'mainTanks' => $mainTanks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tank_id' => 'required|exists:tanks,tank_id',
            'liters' => 'required|numeric|min:0.01',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after_or_equal:started_at',
        ]);

        Transaction::create([
            'transfer_type' => 'vendor_fill',
            'tank_id' => $validated['tank_id'],
            'main_tank_id' => $validated['tank_id'],
            'liters' => $validated['liters'],
            'started_at' => $validated['started_at'],
            'ended_at' => $validated['ended_at'],
            'server_received_at' => now(),
            'entered_by' => $request->user()->id,
            'sync_status' => 'live',
        ]);

        return redirect()->back()->with('success', 'Vendor fill recorded.');
    }
}
