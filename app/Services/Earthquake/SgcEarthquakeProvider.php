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
        $from = now()->subDays(config('earthquakes.sync_days'))->utc()->format('Y-m-d H:i:s');
        try {
            $payload = Http::acceptJson()->retry(2, 300)->timeout(15)->get(config('earthquakes.providers.sgc.url'), [
                'f' => 'geojson', 'where' => "ESP_FECHA >= timestamp '{$from}'", 'outFields' => '*',
                'returnGeometry' => 'true', 'orderByFields' => 'ESP_FECHA DESC',
            ])->throw()->json();
        } catch (Throwable $e) {
            Log::warning('SGC no respondió; se utilizará USGS como respaldo', ['exception' => $e]);
            if ($this->fallback) {
                return $this->fallback->latest();
            }
            throw $e;
        }

        return array_values(array_filter(array_map(fn (array $feature) => $this->normalize($feature), $payload['features'] ?? [])));
    }

    public function normalize(array $feature): ?EarthquakeData
    {
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
