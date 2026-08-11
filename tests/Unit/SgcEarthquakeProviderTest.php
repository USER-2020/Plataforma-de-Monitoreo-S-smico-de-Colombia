<?php

namespace Tests\Unit;

use App\Services\Earthquake\SgcEarthquakeProvider;
use Tests\TestCase;

class SgcEarthquakeProviderTest extends TestCase
{
    public function test_it_normalizes_arcgis_geojson(): void
    {
        $event = (new SgcEarthquakeProvider)->normalize([
            'properties' => ['OBJECTID' => 7, 'ESP_MAGNITUD' => 4.2, 'ESP_PROFUNDIDAD' => 148, 'ESP_FECHA' => 1720000000000, 'MUN_CODIGO' => '68418', 'DEPT_CODIGO' => '68'],
            'geometry' => ['coordinates' => [-73.1, 6.2]],
        ]);
        $this->assertSame('7', $event->externalId);
        $this->assertSame('sgc', $event->source);
        $this->assertSame(148.0, $event->depth);
    }
}
