<?php

namespace Tests\Unit;

use App\Services\Earthquake\UsgsEarthquakeProvider;
use Tests\TestCase;

class UsgsEarthquakeProviderTest extends TestCase
{
    public function test_it_normalizes_geojson(): void
    {
        $event = (new UsgsEarthquakeProvider)->normalize(['id' => 'us1', 'properties' => ['mag' => 3.8, 'magType' => 'ml', 'time' => 1720000000000, 'place' => '10 km de Santander', 'url' => 'https://example.test'], 'geometry' => ['coordinates' => [-73.1, 6.2, 140]]]);
        $this->assertSame('us1', $event->externalId);
        $this->assertSame('Santander', $event->department);
        $this->assertSame(-73.1, $event->longitude);
    }
}
