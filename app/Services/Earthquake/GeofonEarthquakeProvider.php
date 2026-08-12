<?php

namespace App\Services\Earthquake;

use App\Data\EarthquakeData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeofonEarthquakeProvider implements EarthquakeProviderInterface
{
    public function name(): string
    {
        return 'geofon';
    }

    public function latest(): array
    {
        $interval = (int) config('earthquakes.providers.geofon.interval_minutes', 5);
        if (Cache::has('earthquakes.geofon.cooldown')) {
            return [];
        }

        $text = Http::retry(2, 300)->timeout(20)->get(config('earthquakes.providers.geofon.url'), [
            'format' => 'text', 'starttime' => now()->subDays(config('earthquakes.sync_days'))->utc()->toIso8601String(),
            'minlat' => config('earthquakes.coverage.min_lat'), 'maxlat' => config('earthquakes.coverage.max_lat'),
            'minlon' => config('earthquakes.coverage.min_lon'), 'maxlon' => config('earthquakes.coverage.max_lon'),
            'minmagnitude' => 0, 'orderby' => 'time-desc', 'limit' => 1000,
        ])->throw()->body();
        Cache::put('earthquakes.geofon.cooldown', true, now()->addMinutes($interval));

        return collect(preg_split('/\R/', trim($text)))
            ->reject(fn ($line) => $line === '' || str_starts_with($line, '#'))
            ->map(fn ($line) => $this->normalize(explode('|', $line)))
            ->filter()->values()->all();
    }

    public function normalize(array $row): ?EarthquakeData
    {
        if (count($row) < 13 || $row[0] === '' || ! is_numeric($row[2]) || ! is_numeric($row[3])) {
            return null;
        }

        return new EarthquakeData(
            $row[0], (float) ($row[10] ?: 0), $row[9] ?: null, (float) $row[2], (float) $row[3],
            (float) ($row[4] ?: 0), trim($row[12]) ?: 'Región de Colombia', null, null,
            CarbonImmutable::parse($row[1])->utc(), 'geofon',
            'https://geofon.gfz.de/eqinfo/event.php?id='.urlencode($row[0]), ['fdsn_text' => $row],
        );
    }
}
