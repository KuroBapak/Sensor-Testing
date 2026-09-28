<?php

namespace App\Jobs;

use App\Events\AnomalyDetected;
use App\Models\AnomalyLog;
use App\Models\Geofence;
use App\Models\TankGeofenceState;
use App\Models\TankLevelReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckGeofenceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Number of consecutive outside readings before flagging an exit.
     */
    private const EXIT_THRESHOLD = 3;

    public function __construct(
        public TankLevelReading $reading
    ) {}

    /**
     * Check if the tank's position is inside all applicable geofences.
     * Uses hysteresis: only flags exit after EXIT_THRESHOLD consecutive outside readings.
     */
    public function handle(): void
    {
        $reading = $this->reading;

        if (is_null($reading->latitude) || is_null($reading->longitude)) {
            return;
        }

        $tank = $reading->tank;
        if (! $tank || ! in_array($tank->division, ['browser_tank', 'fuel_tanker'])) {
            return;
        }

        $geofences = Geofence::where('is_active', true)->get();

        foreach ($geofences as $geofence) {
            // Check if this geofence applies to this tank's division
            $appliesTo = explode(',', $geofence->applies_to);
            if (! in_array($tank->division, $appliesTo)) {
                continue;
            }

            $isInside = $geofence->containsPoint($reading->latitude, $reading->longitude);

            // Upsert the state record
            $state = TankGeofenceState::updateOrCreate(
                [
                    'tank_id' => $tank->tank_id,
                    'geofence_id' => $geofence->id,
                ],
                [
                    'is_inside' => $isInside,
                    'consecutive_count' => $isInside ? 0 : \DB::raw('consecutive_count + 1'),
                ]
            );

            // Re-read to get the actual consecutive_count value
            $state->refresh();

            if (! $isInside && $state->consecutive_count >= self::EXIT_THRESHOLD) {
                // Check if there's already an open geofence exit anomaly for this tank+geofence
                $alreadyOpen = AnomalyLog::where('anomaly_type', 'geofence_exit')
                    ->where('tank_id', $tank->tank_id)
                    ->where('geofence_id', $geofence->id)
                    ->whereIn('status_investigasi', ['open', 'investigating'])
                    ->exists();

                if (! $alreadyOpen) {
                    $anomaly = AnomalyLog::create([
                        'anomaly_type' => 'geofence_exit',
                        'tank_id' => $tank->tank_id,
                        'geofence_id' => $geofence->id,
                        'device_id' => $reading->device_id,
                        'anomaly_time' => $reading->timestamp,
                        'latitude' => $reading->latitude,
                        'longitude' => $reading->longitude,
                        'position_time' => $reading->timestamp,
                        'status_investigasi' => 'open',
                        'meta' => [
                            'geofence_name' => $geofence->name,
                            'tank_name' => $tank->name,
                            'consecutive_count' => $state->consecutive_count,
                        ],
                    ]);

                    broadcast(new AnomalyDetected($anomaly));
                }
            }
        }
    }
}
