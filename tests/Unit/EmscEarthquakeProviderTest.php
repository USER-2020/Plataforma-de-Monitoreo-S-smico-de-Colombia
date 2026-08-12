<?php

namespace Tests\Unit;

use App\Services\Earthquake\EmscEarthquakeProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmscEarthquakeProviderTest extends TestCase
{
    public function test_it_keeps_events_returned_inside_the_configured_coverage(): void
    {
        $event = fn (string $id, string $region) => ['properties' => ['unid' => $id, 'mag' => 4.2, 'magtype' => 'ml', 'lat' => 6.2, 'lon' => -73.1, 'depth' => 148, 'time' => '2026-08-10T15:30:00Z', 'flynn_region' => $region]];
        Http::fake(['www.seismicportal.eu/*' => Http::response(['features' => [$event('colombia', 'COLOMBIA'), $event('ecuador', 'NEAR COAST OF ECUADOR')]])]);

        $events = (new EmscEarthquakeProvider)->latest();

        $this->assertCount(2, $events);
        $this->assertSame('colombia', $events[0]->externalId);
        $this->assertSame('emsc', $events[0]->source);
        $this->assertSame('ecuador', $events[1]->externalId);
    }
}
