<?php

namespace App\Services\Earthquake;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MultiEarthquakeProvider implements EarthquakeProviderInterface
{
    public function __construct(private array $providers) {}

    public function name(): string
    {
        return 'multi';
    }

    public function latest(): array
    {
        $events = [];
        $successfulProviders = 0;
        $errors = [];
        foreach ($this->providers as $provider) {
            try {
                array_push($events, ...$provider->latest());
                $successfulProviders++;
            } catch (Throwable $exception) {
                $errors[$provider->name()] = $exception->getMessage();
                Log::warning('Proveedor sísmico no disponible', [
                    'provider' => $provider->name(), 'message' => $exception->getMessage(),
                ]);
            }
        }

        if ($successfulProviders === 0) {
            throw new RuntimeException('Ningún proveedor sísmico respondió: '.json_encode($errors, JSON_UNESCAPED_UNICODE));
        }

        return $events;
    }
}
