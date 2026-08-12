<?php

namespace Tests\Unit;

use App\Services\Earthquake\SgcEarthquakeProvider;
use Tests\TestCase;

class SgcEarthquakeProviderTest extends TestCase
{
    public function test_it_normalizes_arcgis_geojson(): void
    {
        $event = (new SgcEarthquakeProvider)->normalize([
            'properties' => ['OBJECTID' => 7, 'ESP_MAGNITUD' => 1.2, 'ESP_PROFUNDIDAD' => 148, 'ESP_FECHA_LONG' => 1720000000000, 'MUN_CODIGO' => '68418', 'DEPT_CODIGO' => '68'],
            'geometry' => ['coordinates' => [-73.1, 6.2]],
        ]);
        $this->assertSame('7', $event->externalId);
        $this->assertSame('sgc', $event->source);
        $this->assertSame(1.2, $event->magnitude);
        $this->assertSame(148.0, $event->depth);
    }

    public function test_it_normalizes_current_sgc_catalog_api(): void
    {
        $event = (new SgcEarthquakeProvider)->normalize([
            'id' => 'SGC2026ptfyjd', 'magnitude' => 3.6, 'mag_type' => 'MLr_vmm',
            'latitude' => 6.0511, 'longitude' => -73.6898, 'depth' => 125,
            'place' => 'Chipatá - Santander, Colombia', 'utc_time' => '2026-08-11 22:29:34',
        ]);

        $this->assertSame('SGC2026ptfyjd', $event->externalId);
        $this->assertSame('Chipatá', $event->municipality);
        $this->assertSame('Santander', $event->department);
        $this->assertSame(3.6, $event->magnitude);
    }
}
