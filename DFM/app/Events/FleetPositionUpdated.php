<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FleetPositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $positions  Array of tank position snapshots.
     */
    public function __construct(
        public array $positions
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('trissan.live.map'),
        ];
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['positions' => $this->positions];
    }

    public function broadcastAs(): string
    {
        return 'position-updated';
    }
}
