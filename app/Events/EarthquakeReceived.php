<?php

namespace App\Events;

use App\Models\Earthquake;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EarthquakeReceived implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Earthquake $earthquake) {}

    public function broadcastOn(): array
    {
        return [new Channel('earthquakes')];
    }

    public function broadcastAs(): string
    {
        return 'earthquake.received';
    }

    public function broadcastWith(): array
    {
        return ['earthquake' => $this->earthquake->toArray()];
    }
}
