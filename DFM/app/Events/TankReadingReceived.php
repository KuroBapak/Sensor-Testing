<?php

namespace App\Events;

use App\Models\Tank;
use App\Models\TankLevelReading;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TankReadingReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $tankId
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        $tank = Tank::find($this->tankId);
        $aggregate = $tank && $tank->division === 'main_tank'
            ? 'trissan.live.main-tank'
            : 'trissan.live.mobile-tanks';

        return [
            new PrivateChannel($aggregate),
            new PrivateChannel("trissan.tank.{$this->tankId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'tank-reading';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $latest = TankLevelReading::where('tank_id', $this->tankId)
            ->latest('timestamp')
            ->first();

        return [
            'tank_id' => $this->tankId,
            'level_liters' => $latest?->level_liters,
            'latitude' => $latest?->latitude,
            'longitude' => $latest?->longitude,
            'timestamp' => $latest?->timestamp?->toIso8601String(),
        ];
    }
}
