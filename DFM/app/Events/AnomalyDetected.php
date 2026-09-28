<?php

namespace App\Events;

use App\Models\AnomalyLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnomalyDetected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public AnomalyLog $anomalyLog
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('trissan.alarms'),
            new PrivateChannel('trissan.live.alarms'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'anomaly-detected';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->anomalyLog->id,
            'anomaly_type' => $this->anomalyLog->anomaly_type,
            'tank_id' => $this->anomalyLog->tank_id,
            'volume_diff' => $this->anomalyLog->volume_diff,
            'anomaly_time' => $this->anomalyLog->anomaly_time?->toIso8601String(),
            'status_investigasi' => $this->anomalyLog->status_investigasi,
            'latitude' => $this->anomalyLog->latitude,
            'longitude' => $this->anomalyLog->longitude,
        ];
    }
}
