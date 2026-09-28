<?php

namespace App\Http\Controllers\Api;

use App\Events\FleetPositionUpdated;
use App\Events\TankReadingReceived;
use App\Http\Controllers\Controller;
use App\Jobs\CheckGeofenceJob;
use App\Models\HardwareDevice;
use App\Models\TankLevelReading;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvlIngestionController extends Controller
{
    /**
     * Internal API: POST /api/internal/ingest/avl
     * Receives AVL data from FMC225 via local TCP-to-HTTP bridge.
     * No auth — localhost access only (handled by firewall / middleware).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'imei' => 'required|string',
            'records' => 'required|array|min:1|max:200',
            'records.*.timestamp' => 'required|date',
            'records.*.latitude' => 'nullable|numeric|between:-90,90',
            'records.*.longitude' => 'nullable|numeric|between:-180,180',
            'records.*.satellites' => 'nullable|integer|min:0|max:255',
            'records.*.fuel_level_liters' => 'nullable|numeric|min:0',
        ]);

        // Resolve the hardware device by IMEI
        $device = HardwareDevice::where('device_id', $validated['imei'])->first();
        if (! $device) {
            return response()->json(['error' => 'Unknown IMEI'], 404);
        }

        if (! $device->tank_id) {
            return response()->json(['error' => 'Device not assigned to a tank'], 422);
        }

        $created = 0;

        foreach ($validated['records'] as $record) {
            $timestamp = Carbon::parse($record['timestamp']);

            $rtcOutOfBounds = $timestamp->isAfter(now()->addDay())
                || $timestamp->isBefore(now()->subWeek());

            $levelReading = TankLevelReading::updateOrCreate(
                [
                    'device_id' => $device->device_id,
                    'timestamp' => $timestamp,
                ],
                [
                    'tank_id' => $device->tank_id,
                    'level_liters' => $record['fuel_level_liters'] ?? 0,
                    'latitude' => $record['latitude'] ?? null,
                    'longitude' => $record['longitude'] ?? null,
                    'satellites' => $record['satellites'] ?? null,
                    'server_received_at' => now(),
                    'rtc_out_of_bounds' => $rtcOutOfBounds,
                ]
            );

            if ($levelReading->wasRecentlyCreated) {
                $created++;
                CheckGeofenceJob::dispatch($levelReading);
            }
        }

        $device->update(['last_seen' => now()]);

        // Broadcast updates
        broadcast(new TankReadingReceived($device->tank_id))->toOthers();

        $latestReading = TankLevelReading::where('tank_id', $device->tank_id)
            ->whereNotNull('latitude')
            ->orderByDesc('timestamp')
            ->first();

        if ($latestReading) {
            broadcast(new FleetPositionUpdated([
                [
                    'tank_id' => $latestReading->tank_id,
                    'lat' => $latestReading->latitude,
                    'lng' => $latestReading->longitude,
                    'level_liters' => $latestReading->level_liters,
                    'timestamp' => $latestReading->timestamp->toIso8601String(),
                ],
            ]))->toOthers();
        }

        return response()->json([
            'accepted' => $created,
            'device_id' => $device->device_id,
        ], $created > 0 ? 201 : 200);
    }
}
