<?php

namespace App\Services\Earthquake;

use App\Data\EarthquakeData;

interface EarthquakeProviderInterface
{
    /** @return array<EarthquakeData> */
    public function latest(): array;

    public function name(): string;
}
