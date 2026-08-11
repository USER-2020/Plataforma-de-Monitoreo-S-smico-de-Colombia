<?php

namespace Tests\Feature;

use App\Events\EarthquakeReceived;
use App\Services\Earthquake\EmscEarthquakeStream;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmscRealtimeStreamTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_deduplicates_and_broadcasts_colombian_events(): void
    {
        Event::fake([EarthquakeReceived::class]);
        Http::fake(['geoportal.dane.gov.co/*' => Http::response(['features' => [['attributes' => [
            'DPTO_CCDGO' => '68', 'MPIO_CCDGO' => '217', 'MPIO_CDPMP' => '68217',
            'DPTO_CNMBRE' => 'SANTANDER', 'MPIO_CNMBRE' => 'COROMORO',
        ]]]])]);
        $message = json_encode(['action' => 'create', 'data' => ['properties' => [
            'unid' => '20260810_0000001', 'mag' => 4.3, 'magtype' => 'ml',
            'lat' => 6.2, 'lon' => -73.1, 'depth' => 148,
            'time' => '2026-08-10T15:30:00Z', 'flynn_region' => 'COLOMBIA',
        ]]]);
        $stream = app(EmscEarthquakeStream::class);

        $stream->ingest($message);
        $stream->ingest($message);

        $this->assertDatabaseCount('earthquakes', 1);
        $this->assertDatabaseHas('earthquakes', ['external_id' => '20260810_0000001', 'source' => 'emsc', 'municipality' => 'COROMORO', 'municipality_code' => '68217', 'department' => 'SANTANDER']);
        Event::assertDispatched(EarthquakeReceived::class, 2);
    }

    public function test_it_ignores_events_outside_colombia(): void
    {
        $message = json_encode(['data' => ['properties' => [
            'unid' => 'outside', 'mag' => 5, 'lat' => 40.4, 'lon' => -3.7,
            'depth' => 10, 'time' => '2026-08-10T15:30:00Z', 'flynn_region' => 'SPAIN',
        ]]]);

        $this->assertNull(app(EmscEarthquakeStream::class)->ingest($message));
        $this->assertDatabaseCount('earthquakes', 0);
    }
}
