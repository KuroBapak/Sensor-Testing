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

class SiteReadingController extends Controller
{
    /**
     * Edge API: POST /api/v1/site/readings
     * Receives periodic tank level + GPS readings from RUT956 / FMC225.
     * Accepts a batch of readings in a single payload.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'readings' => 'required|array|min:1|max:100',
            'readings.*.device_id' => 'required|string|exists:hardware_devices,device_id',
            'readings.*.tank_id' => 'required|exists:tanks,tank_id',
            'readings.*.level_liters' => 'required|numeric|min:0',
            'readings.*.latitude' => 'nullable|numeric|between:-90,90',
            'readings.*.longitude' => 'nullable|numeric|between:-180,180',
            'readings.*.satellites' => 'nullable|integer|min:0|max:255',
            'readings.*.timestamp' => 'required|date',
        ]);

        $created = 0;
        $skipped = 0;
        $affectedTankIds = [];

        foreach ($validated['readings'] as $reading) {
            $timestamp = Carbon::parse($reading['timestamp']);

            // RTC sanity check: reject readings > 24h in the future or > 7 days in the past
            $rtcOutOfBounds = $timestamp->isAfter(now()->addDay())
                || $timestamp->isBefore(now()->subWeek());

            $record = TankLevelReading::updateOrCreate(
                [
                    'device_id' => $reading['device_id'],
                    'timestamp' => $timestamp,
                ],
                [
                    'tank_id' => $reading['tank_id'],
                    'level_liters' => $reading['level_liters'],
                    'latitude' => $reading['latitude'] ?? null,
                    'longitude' => $reading['longitude'] ?? null,
                    'satellites' => $reading['satellites'] ?? null,
                    'server_received_at' => now(),
                    'rtc_out_of_bounds' => $rtcOutOfBounds,
                ]
            );

            if ($record->wasRecentlyCreated) {
                $created++;
                $affectedTankIds[] = $reading['tank_id'];

                // Dispatch geofence check for mobile tanks
                CheckGeofenceJob::dispatch($record);
            } else {
                $skipped++;
            }
        }

        // Update last_seen for all devices in this batch
        $deviceIds = collect($validated['readings'])->pluck('device_id')->unique();
        HardwareDevice::whereIn('device_id', $deviceIds)->update(['last_seen' => now()]);

        // Broadcast tank updates for real-time dashboards
        foreach (array_unique($affectedTankIds) as $tankId) {
            broadcast(new TankReadingReceived($tankId))->toOthers();
        }

        // Broadcast fleet positions for map
        if (! empty($affectedTankIds)) {
            $positions = TankLevelReading::whereIn('tank_id', array_unique($affectedTankIds))
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderByDesc('timestamp')
                ->get()
                ->unique('tank_id')
                ->map(fn ($r) => [
                    'tank_id' => $r->tank_id,
                    'lat' => $r->latitude,
                    'lng' => $r->longitude,
                    'level_liters' => $r->level_liters,
                    'timestamp' => $r->timestamp->toIso8601String(),
                ])
                ->values()
                ->toArray();

            broadcast(new FleetPositionUpdated($positions))->toOthers();
        }

        return response()->json([
            'created' => $created,
            'skipped' => $skipped,
        ], $created > 0 ? 201 : 200);
    }
}
