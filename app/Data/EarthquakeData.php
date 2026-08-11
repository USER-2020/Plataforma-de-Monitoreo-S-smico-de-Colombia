<?php

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class EarthquakeData
{
    public function __construct(
        public string $externalId,
        public float $magnitude,
        public ?string $magnitudeType,
        public float $latitude,
        public float $longitude,
        public float $depth,
        public string $place,
        public ?string $municipality,
        public ?string $department,
        public CarbonImmutable $occurredAt,
        public string $source,
        public ?string $sourceUrl,
        public array $rawData,
    ) {}

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId, 'source' => $this->source,
            'magnitude' => $this->magnitude, 'magnitude_type' => $this->magnitudeType,
            'latitude' => $this->latitude, 'longitude' => $this->longitude,
            'depth_km' => $this->depth, 'place' => $this->place,
            'municipality' => $this->municipality, 'department' => $this->department,
            'country' => 'Colombia', 'occurred_at' => $this->occurredAt->utc(),
            'source_url' => $this->sourceUrl, 'raw_data' => $this->rawData,
        ];
    }
}
