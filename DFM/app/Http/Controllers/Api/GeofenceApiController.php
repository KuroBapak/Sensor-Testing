<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Geofence;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeofenceApiController extends Controller
{
    public function index(): JsonResponse
    {
        $geofences = Geofence::with(['createdByUser:id,name', 'updatedByUser:id,name'])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $geofences]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'coordinates' => 'required|array|min:3',
            'coordinates.*' => 'array|size:2',
            'coordinates.*.0' => 'required|numeric|between:-90,90',
            'coordinates.*.1' => 'required|numeric|between:-180,180',
            'applies_to' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $site = Site::firstOrFail();

        $geofence = Geofence::create([
            'site_id' => $site->id,
            'name' => $validated['name'],
            'coordinates' => $validated['coordinates'],
            'applies_to' => $validated['applies_to'],
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $geofence], 201);
    }

    public function update(Request $request, Geofence $geofence): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'coordinates' => 'sometimes|array|min:3',
            'coordinates.*' => 'array|size:2',
            'coordinates.*.0' => 'required|numeric|between:-90,90',
            'coordinates.*.1' => 'required|numeric|between:-180,180',
            'applies_to' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $geofence->update(array_merge($validated, [
            'updated_by' => $request->user()->id,
        ]));

        return response()->json(['data' => $geofence->fresh()]);
    }

    public function destroy(Geofence $geofence): JsonResponse
    {
        $geofence->delete();

        return response()->json(null, 204);
    }
}
