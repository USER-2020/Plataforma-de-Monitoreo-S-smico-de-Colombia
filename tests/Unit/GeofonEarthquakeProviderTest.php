<?php

namespace Tests\Unit;

use App\Services\Earthquake\GeofonEarthquakeProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeofonEarthquakeProviderTest extends TestCase
{
    public function test_it_uses_supported_parameters_and_orders_results_locally(): void
    {
        Cache::forget('earthquakes.geofon.cooldown');
        Http::fake(['geofon.gfz.de/*' => Http::response(implode("\n", [
            '#EventID|Time|Latitude|Longitude|Depth|Author|Catalog|Contributor|ContributorID|MagType|Magnitude|MagAuthor|EventLocationName',
            'old|2026-08-11T10:00:00Z|4.5|-74.1|20|GFZ||||mb|2.1||COLOMBIA',
            'new|2026-08-12T10:00:00Z|5.5|-75.1|30|GFZ||||mb|3.1||COLOMBIA',
        ]))]);

        $events = (new GeofonEarthquakeProvider)->latest();

        $this->assertSame(['new', 'old'], collect($events)->pluck('externalId')->all());
        Http::assertSent(fn (Request $request) => ! isset($request->data()['orderby']));
    }
}
