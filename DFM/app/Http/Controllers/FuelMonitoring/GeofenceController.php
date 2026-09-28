<?php

namespace App\Http\Controllers\FuelMonitoring;

use App\Http\Controllers\Controller;
use App\Models\Geofence;
use Inertia\Inertia;
use Inertia\Response;

class GeofenceController extends Controller
{
    public function index(): Response
    {
        $geofences = Geofence::query()
            ->orderBy('name')
            ->get(['id', 'name', 'applies_to', 'coordinates', 'is_active', 'created_at', 'updated_at']);

        return Inertia::render('admin/geofences/index', [
            'geofences' => $geofences,
        ]);
    }
}
