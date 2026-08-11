<?php

namespace App\Jobs;

use App\Services\Earthquake\EmscEarthquakeStream;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessEmscEarthquake implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public string $message) {}

    public function handle(EmscEarthquakeStream $stream): void
    {
        $stream->ingest($this->message);
    }
}
