<?php

namespace App\Http\Controllers\Api;

use App\Events\AlarmCreated;
use App\Http\Controllers\Controller;
use App\Models\AnomalyLog;
use App\Models\HardwareDevice;
use App\Models\RfidTag;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanRejectionController extends Controller
{
    /**
     * Edge API: POST /api/v1/scan-rejections
     * Logs rejected RFID scans as unauthorized_scan alarms.
     * PRD §2.A, §5
     *
     * Request: {
     *   "device_event_id": "uuid",
     *   "device_id": "string",
     *   "raw_tag_uid": "string",
     *   "reason": "string",
     *   "timestamp": "ISO8601 string"
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_event_id' => 'required|uuid',
            'device_id' => 'required|string|exists:hardware_devices,device_id',
            'raw_tag_uid' => 'required|string',
            'reason' => 'required|string',
            'timestamp' => 'required|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $timestamp = Carbon::parse($validated['timestamp']);

        // Check if raw_tag_uid matches a known tag_id in rfid_tags to satisfy foreign key
        $tagExists = RfidTag::where('tag_id', $validated['raw_tag_uid'])->exists();

        // Get tank_id associated with device if any
        $device = HardwareDevice::where('device_id', $validated['device_id'])->first();

        // Dedup on device_id + device_event_id
        $alarm = AnomalyLog::firstOrCreate(
            [
                'device_id' => $validated['device_id'],
                'device_event_id' => $validated['device_event_id'],
            ],
            [
                'anomaly_type' => 'unauthorized_scan',
                'tank_id' => $device?->tank_id,
                'tag_id' => $tagExists ? $validated['raw_tag_uid'] : null,
                'anomaly_time' => $timestamp,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'position_time' => $timestamp,
                'status_investigasi' => 'open',
                'meta' => [
                    'reason' => $validated['reason'],
                    'raw_tag_uid' => $validated['raw_tag_uid'],
                ],
            ]
        );

        // Update device last_seen
        $device?->update(['last_seen' => now()]);

        // Broadcast the new alarm if it was just created
        if ($alarm->wasRecentlyCreated) {
            broadcast(new AlarmCreated($alarm))->toOthers();
        }

        return response()->json([
            'id' => $alarm->id,
            'status' => $alarm->wasRecentlyCreated ? 'created' : 'already_recorded',
        ], $alarm->wasRecentlyCreated ? 201 : 200);
    }
}
