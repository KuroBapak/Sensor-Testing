<?php

namespace App\Events;

use App\Models\AnomalyLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlarmCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public AnomalyLog $alarm) {}

    public function broadcastOn(): Channel
    {
        return new Channel('trissan.live.alarms');
    }

    public function broadcastAs(): string
    {
        return 'alarm-created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->alarm->id,
            'anomaly_type' => $this->alarm->anomaly_type,
            'tank_id' => $this->alarm->tank_id,
            'anomaly_time' => $this->alarm->anomaly_time?->toIso8601String(),
            'status_investigasi' => $this->alarm->status_investigasi,
        ];
    }
}
