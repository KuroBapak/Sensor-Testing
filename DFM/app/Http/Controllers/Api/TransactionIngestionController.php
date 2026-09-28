<?php

namespace App\Http\Controllers\Api;

use App\Events\TankReadingReceived;
use App\Http\Controllers\Controller;
use App\Jobs\DetectAnomalyJob;
use App\Models\HardwareDevice;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionIngestionController extends Controller
{
    /**
     * Edge API: POST /api/v1/transactions
     * Receives fuel transfer transactions from Teltonika RUT956.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_txn_id' => 'required|uuid',
            'device_id' => 'required|string|exists:hardware_devices,device_id',
            'transfer_type' => 'required|in:fill_to_main,dispense_to_browser',
            'tag_id' => 'nullable|string',
            'tank_id' => 'required|exists:tanks,tank_id',
            'main_tank_id' => 'nullable|exists:tanks,tank_id',
            'liters' => 'required|numeric|min:0.01',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after_or_equal:started_at',
        ]);

        // Idempotent upsert using device_id + device_txn_id unique index
        $transaction = Transaction::updateOrCreate(
            [
                'device_id' => $validated['device_id'],
                'device_txn_id' => $validated['device_txn_id'],
            ],
            array_merge($validated, [
                'server_received_at' => now(),
                'sync_status' => 'live',
            ])
        );

        // Update device last_seen
        HardwareDevice::where('device_id', $validated['device_id'])
            ->update(['last_seen' => now()]);

        // Dispatch anomaly detection
        DetectAnomalyJob::dispatch($transaction);

        // Broadcast for real-time dashboards
        broadcast(new TankReadingReceived($transaction->tank_id))->toOthers();

        return response()->json([
            'id' => $transaction->id,
            'status' => $transaction->wasRecentlyCreated ? 'created' : 'updated',
        ], $transaction->wasRecentlyCreated ? 201 : 200);
    }
}
