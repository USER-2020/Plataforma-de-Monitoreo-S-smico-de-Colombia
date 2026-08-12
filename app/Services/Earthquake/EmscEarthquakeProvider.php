<?php

namespace App\Services\Earthquake;

use App\Data\EarthquakeData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class EmscEarthquakeProvider implements EarthquakeProviderInterface
{
    public function name(): string
    {
        return 'emsc';
    }

    public function latest(): array
    {
        $json = Http::acceptJson()->retry(2, 300)->timeout(20)->get(config('earthquakes.providers.emsc.url'), ['format' => 'json', 'limit' => 1000, 'orderby' => 'time', 'start' => now()->subDays(config('earthquakes.sync_days'))->utc()->toIso8601String(), 'minmag' => 0, 'minlat' => config('earthquakes.coverage.min_lat'), 'maxlat' => config('earthquakes.coverage.max_lat'), 'minlon' => config('earthquakes.coverage.min_lon'), 'maxlon' => config('earthquakes.coverage.max_lon')])->throw()->json();

        return collect($json['features'] ?? [])
            ->filter(fn (array $feature) => str_contains(
                strtoupper((string) data_get($feature, 'properties.flynn_region')),
                'COLOMBIA',
            ))
            ->map(fn (array $f) => $this->normalize($f))->filter()->values()->all();
    }

    public function normalize(array $feature): ?EarthquakeData
    {
        $p = $feature['properties'] ?? [];
        if (! isset($p['unid'],$p['mag'],$p['lat'],$p['lon'],$p['time'])) {
            return null;
        }

        return new EarthquakeData((string) $p['unid'], (float) $p['mag'], $p['magtype'] ?? null, (float) $p['lat'], (float) $p['lon'], (float) ($p['depth'] ?? 0), (string) ($p['flynn_region'] ?? 'Colombia'), null, null, CarbonImmutable::parse($p['time'])->utc(), 'emsc', 'https://www.seismicportal.eu/eventdetails.html?unid='.$p['unid'], $feature);
    }
}
