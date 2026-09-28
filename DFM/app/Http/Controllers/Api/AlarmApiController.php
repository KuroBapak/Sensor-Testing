<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnomalyLog;
use App\Models\AnomalyRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlarmApiController extends Controller
{
    /**
     * POST /api/v1/alerts/{anomalyLog}/read
     * Mark an anomaly as read by current user, transitioning status open -> investigating.
     */
    public function markRead(Request $request, AnomalyLog $anomalyLog): JsonResponse
    {
        AnomalyRead::firstOrCreate(
            ['anomaly_id' => $anomalyLog->id, 'user_id' => $request->user()->id],
            ['read_at' => now()]
        );

        if ($anomalyLog->status_investigasi === 'open') {
            $anomalyLog->update([
                'status_investigasi' => 'investigating',
            ]);
        }

        return response()->json(['data' => $anomalyLog->fresh()]);
    }

    /**
     * PUT /api/v1/alerts/{anomalyLog}/status
     * Update the investigation status with resolution details.
     */
    public function updateStatus(Request $request, AnomalyLog $anomalyLog): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,investigating,resolved,false_positive',
            'resolution_note' => 'nullable|string|max:2000',
        ]);

        $updateData = [
            'status_investigasi' => $validated['status'],
            'resolution_note' => $validated['resolution_note'] ?? $anomalyLog->resolution_note,
        ];

        // Set resolved metadata when closing
        if (in_array($validated['status'], ['resolved', 'false_positive'])) {
            $updateData['resolved_by'] = $request->user()->id;
            $updateData['resolved_at'] = now();
        }

        // Clear resolved metadata when re-opening
        if (in_array($validated['status'], ['open', 'investigating'])) {
            $updateData['resolved_by'] = null;
            $updateData['resolved_at'] = null;
        }

        $anomalyLog->update($updateData);

        return response()->json(['data' => $anomalyLog->fresh()->load('resolvedBy:id,name')]);
    }
}
