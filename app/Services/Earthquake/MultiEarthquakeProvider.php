<?php

namespace App\Services\Earthquake;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MultiEarthquakeProvider implements EarthquakeProviderInterface
{
    private array $diagnostics = [];

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
        $this->diagnostics = [];
        foreach ($this->providers as $provider) {
            try {
                $providerEvents = $provider->latest();
                array_push($events, ...$providerEvents);
                $successfulProviders++;
                $this->diagnostics[$provider->name()] = [
                    'status' => 'success',
                    'received' => count($providerEvents),
                    'error' => null,
                ];
            } catch (Throwable $exception) {
                $errors[$provider->name()] = $exception->getMessage();
                $this->diagnostics[$provider->name()] = [
                    'status' => 'failed',
                    'received' => 0,
                    'error' => $exception->getMessage(),
                ];
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

    public function diagnostics(): array
    {
        return $this->diagnostics;
    }
}
