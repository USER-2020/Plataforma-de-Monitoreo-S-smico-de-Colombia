<?php

namespace App\Services\Earthquake;

use App\Data\EarthquakeData;
use App\Events\EarthquakeReceived;
use App\Models\Earthquake;
use App\Services\Geography\DaneLocationService;
use Carbon\CarbonImmutable;

class EmscEarthquakeStream
{
    public function __construct(private EarthquakeService $service, private DaneLocationService $dane) {}

    public function ingest(string $message): ?Earthquake
    {
        $payload = json_decode($message, true, flags: JSON_THROW_ON_ERROR);
        $properties = $payload['data']['properties'] ?? null;
        if (! is_array($properties) || ! $this->isInColombia($properties)) {
            return null;
        }
        $data = $this->normalize($payload, $properties);
        $location = $this->dane->locate($data->latitude, $data->longitude);
        $earthquake = Earthquake::updateOrCreate(
            ['external_id' => $data->externalId, 'source' => $data->source],
            [...$data->toArray(), ...($location ?? [])],
        );
        if ($earthquake->wasRecentlyCreated) {
            $this->service->notifySubscribers($earthquake);
        }
        EarthquakeReceived::dispatch($earthquake->fresh());

        return $earthquake;
    }

    public function normalize(array $payload, ?array $properties = null): EarthquakeData
    {
        $p = $properties ?? $payload['data']['properties'];

        return new EarthquakeData(
            (string) $p['unid'], (float) $p['mag'], $p['magtype'] ?? null,
            (float) $p['lat'], (float) $p['lon'], (float) ($p['depth'] ?? 0),
            (string) ($p['flynn_region'] ?? 'Colombia'), null, null,
            CarbonImmutable::parse($p['time'])->utc(), 'emsc',
            'https://www.seismicportal.eu/eventdetails.html?unid='.$p['unid'], $payload,
        );
    }

    private function isInColombia(array $properties): bool
    {
        $lat = (float) ($properties['lat'] ?? 999);
        $lon = (float) ($properties['lon'] ?? 999);

        return $lat >= -4.5 && $lat <= 13.7 && $lon >= -82 && $lon <= -66;
    }
}
