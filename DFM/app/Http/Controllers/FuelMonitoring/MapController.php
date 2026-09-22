<?php

namespace App\Http\Controllers\FuelMonitoring;

use App\Http\Controllers\Controller;
use App\Models\Geofence;
use App\Models\Tank;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    public function index(): Response
    {
        $tanks = Tank::whereIn('division', ['browser_tank', 'fuel_tanker'])
            ->get(['tank_id as id', 'name', 'division as type', 'capacity_liters as capacity'])
            ->map(function ($tank) {
                // Mock live coordinates for telemetry simulation.
                return [
                    'id' => $tank->id,
                    'name' => $tank->name,
                    'type' => $tank->type,
                    'capacity' => $tank->capacity,
                    'status' => 'active',
                    'lat' => -2.6000 + (rand(-100, 100) / 10000),
                    'lng' => 118.0000 + (rand(-100, 100) / 10000),
                    'heading' => rand(0, 359),
                    'speed' => rand(0, 60),
                    'last_updated' => now()->toIso8601String(),
                ];
            });

        // Provide demo units if database has no registered mobile tanks yet
        if ($tanks->isEmpty()) {
            $tanks = collect([
                [
                    'id' => 'BT-01',
                    'name' => 'Browser Tank 01',
                    'type' => 'browser_tank',
                    'capacity' => 5000,
                    'status' => 'active',
                    'lat' => -2.6015,
                    'lng' => 118.0020,
                    'heading' => 45,
                    'speed' => 24,
                    'last_updated' => now()->toIso8601String(),
                ],
                [
                    'id' => 'BT-02',
                    'name' => 'Browser Tank 02',
                    'type' => 'browser_tank',
                    'capacity' => 5000,
                    'status' => 'active',
                    'lat' => -2.5980,
                    'lng' => 117.9975,
                    'heading' => 180,
                    'speed' => 0,
                    'last_updated' => now()->toIso8601String(),
                ],
                [
                    'id' => 'FT-01',
                    'name' => 'Fuel Tanker 01',
                    'type' => 'fuel_tanker',
                    'capacity' => 16000,
                    'status' => 'active',
                    'lat' => -2.6040,
                    'lng' => 118.0050,
                    'heading' => 290,
                    'speed' => 38,
                    'last_updated' => now()->toIso8601String(),
                ],
                [
                    'id' => 'FT-02',
                    'name' => 'Fuel Tanker 02 (Signal Lost)',
                    'type' => 'fuel_tanker',
                    'capacity' => 16000,
                    'status' => 'signal_lost',
                    'lat' => -2.5950,
                    'lng' => 118.0090,
                    'heading' => 120,
                    'speed' => 0,
                    'last_updated' => now()->subMinutes(25)->toIso8601String(),
                ],
            ]);
        }

        $geofences = Geofence::all(['id', 'name', 'applies_to', 'coordinates', 'is_active']);

        // Provide demo geofence boundaries if none exist yet
        if ($geofences->isEmpty()) {
            $geofences = collect([
                [
                    'id' => 1,
                    'name' => 'Mining Site Main Perimeter',
                    'applies_to' => 'browser_tank,fuel_tanker',
                    'is_active' => true,
                    'coordinates' => [
                        [-2.5900, 117.9900],
                        [-2.5900, 118.0150],
                        [-2.6100, 118.0150],
                        [-2.6100, 117.9900],
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'Fuel Depot & Workshop Zone',
                    'applies_to' => 'fuel_tanker',
                    'is_active' => true,
                    'coordinates' => [
                        [-2.5980, 117.9980],
                        [-2.5980, 118.0060],
                        [-2.6050, 118.0060],
                        [-2.6050, 117.9980],
                    ],
                ],
            ]);
        }

        return Inertia::render('fuel-monitoring/map', [
            'tanks' => $tanks,
            'geofences' => $geofences,
        ]);
    }
}
