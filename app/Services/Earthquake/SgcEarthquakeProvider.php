<?php

namespace App\Services\Earthquake;

use App\Data\EarthquakeData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SgcEarthquakeProvider implements EarthquakeProviderInterface
{
    public function __construct(private ?UsgsEarthquakeProvider $fallback = null) {}

    public function name(): string
    {
        return 'sgc';
    }

    public function latest(): array
    {
        $timezone = config('app.timezone', 'America/Bogota');
        $query = [
            'local_time_after' => now()->subHours(config('earthquakes.providers.sgc.sync_hours', 48))->timezone($timezone)->format('Y-m-d H:i:s'),
            'local_time_before' => now()->timezone($timezone)->format('Y-m-d H:i:s'),
        ];
        try {
            $payload = Http::acceptJson()->asJson()->retry(2, 300)->timeout(20)
                ->post(config('earthquakes.providers.sgc.url').'?page=1', $query)->throw()->json();
        } catch (Throwable $e) {
            Log::warning('SGC no respondió; se utilizará USGS como respaldo', ['exception' => $e]);
            if ($this->fallback) {
                return $this->fallback->latest();
            }
            throw $e;
        }

        $events = data_get($payload, 'results.results', []);
        $pages = min(10, (int) ceil(((int) ($payload['count'] ?? count($events))) / 100));
        for ($page = 2; $page <= $pages; $page++) {
            $next = Http::acceptJson()->asJson()->retry(2, 300)->timeout(20)
                ->post(config('earthquakes.providers.sgc.url').'?page='.$page, $query)->throw()->json();
            array_push($events, ...data_get($next, 'results.results', []));
        }

        return array_values(array_filter(array_map(fn (array $event) => $this->normalize($event), $events)));
    }

    public function normalize(array $feature): ?EarthquakeData
    {
        if (isset($feature['id'], $feature['magnitude'], $feature['latitude'], $feature['longitude'], $feature['utc_time'])) {
            $parts = array_map('trim', explode(' - ', (string) ($feature['place'] ?? ''), 2));
            $municipality = $parts[0] ?: null;
            $department = isset($parts[1]) ? trim(explode(',', $parts[1])[0]) : null;

            return new EarthquakeData(
                (string) $feature['id'], (float) $feature['magnitude'], $feature['mag_type'] ?? null,
                (float) $feature['latitude'], (float) $feature['longitude'], (float) ($feature['depth'] ?? 0),
                (string) ($feature['place'] ?? 'Colombia'), $municipality, $department,
                CarbonImmutable::parse($feature['utc_time'], 'UTC')->utc(), 'sgc',
                'https://www.sgc.gov.co/detallesismo/'.$feature['id'].'/resumen', $feature,
            );
        }

        $p = $feature['properties'] ?? [];
        $c = $feature['geometry']['coordinates'] ?? [];
        $id = $p['ESP_ID_EVENTO_TXT'] ?? $p['OBJECTID'] ?? null;
        $date = $p['ESP_FECHA'] ?? $p['ESP_FECHA_LONG'] ?? null;
        if ($id === null || $date === null || ! isset($p['ESP_MAGNITUD'], $c[0], $c[1])) {
            return null;
        }
        $occurredAt = is_numeric($date) ? CarbonImmutable::createFromTimestampMs((int) $date)->utc() : CarbonImmutable::parse((string) $date, 'UTC')->utc();
        $municipality = isset($p['MUN_CODIGO']) ? 'Municipio código '.$p['MUN_CODIGO'] : null;
        $department = isset($p['DEPT_CODIGO']) ? 'Departamento código '.$p['DEPT_CODIGO'] : null;

        return new EarthquakeData((string) $id, (float) $p['ESP_MAGNITUD'], $p['ESP_FUENTE_MAGNITUD'] ?? null, (float) $c[1], (float) $c[0], (float) ($p['ESP_PROFUNDIDAD'] ?? 0), $municipality ?? 'Colombia', $municipality, $department, $occurredAt, 'sgc', config('earthquakes.providers.sgc.catalog_url'), $feature);
    }
}
