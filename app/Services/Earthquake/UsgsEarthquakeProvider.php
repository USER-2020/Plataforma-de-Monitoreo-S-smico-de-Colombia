<?php

namespace App\Services\Earthquake;

use App\Data\EarthquakeData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class UsgsEarthquakeProvider implements EarthquakeProviderInterface
{
    public function name(): string
    {
        return 'usgs';
    }

    public function latest(): array
    {
        $response = Http::acceptJson()->retry(2, 300)->timeout(15)->get(config('earthquakes.providers.usgs.url'), [
            'format' => 'geojson', 'starttime' => now()->subDays(config('earthquakes.sync_days'))->toIso8601String(),
            'minmagnitude' => 0,
            'minlatitude' => config('earthquakes.coverage.min_lat'), 'maxlatitude' => config('earthquakes.coverage.max_lat'),
            'minlongitude' => config('earthquakes.coverage.min_lon'), 'maxlongitude' => config('earthquakes.coverage.max_lon'),
        ])->throw()->json();

        return array_values(array_filter(array_map(fn (array $feature) => $this->normalize($feature), $response['features'] ?? [])));
    }

    public function normalize(array $feature): ?EarthquakeData
    {
        $p = $feature['properties'] ?? [];
        $c = $feature['geometry']['coordinates'] ?? [];
        if (! isset($feature['id'],$p['mag'],$p['time'],$c[0],$c[1])) {
            return null;
        }
        $place = (string) ($p['place'] ?? 'Ubicación no reportada');

        return new EarthquakeData((string) $feature['id'], (float) $p['mag'], $p['magType'] ?? null, (float) $c[1], (float) $c[0], (float) ($c[2] ?? 0), $place, null, $this->departmentFrom($place), CarbonImmutable::createFromTimestampMs((int) $p['time'])->utc(), 'usgs', $p['url'] ?? null, $feature);
    }

    private function departmentFrom(string $place): ?string
    {
        $departments = config('earthquakes.departments');
        foreach ($departments as $department) {
            if (str_contains(mb_strtolower($place), mb_strtolower($department))) {
                return $department;
            }
        }

        return null;
    }
}
