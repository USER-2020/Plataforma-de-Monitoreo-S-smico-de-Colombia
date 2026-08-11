<?php

namespace App\Services\Geography;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DaneLocationService
{
    public function locate(float $latitude, float $longitude): ?array
    {
        $key = sprintf('dane.location.%.4f.%.4f', $latitude, $longitude);

        return Cache::remember($key, now()->addDays(30), function () use ($latitude, $longitude) {
            try {
                $json = Http::acceptJson()->retry(2, 250)->timeout(10)
                    ->get(config('earthquakes.dane_municipalities_url').'/query', [
                        'f' => 'json', 'where' => '1=1',
                        'geometry' => $longitude.','.$latitude,
                        'geometryType' => 'esriGeometryPoint', 'inSR' => 4326,
                        'spatialRel' => 'esriSpatialRelIntersects',
                        'outFields' => 'DPTO_CCDGO,MPIO_CCDGO,MPIO_CDPMP,DPTO_CNMBRE,MPIO_CNMBRE',
                        'returnGeometry' => 'false',
                    ])->throw()->json();
                $attributes = $json['features'][0]['attributes'] ?? null;
                if (! $attributes) {
                    return null;
                }

                return [
                    'municipality' => $attributes['MPIO_CNMBRE'],
                    'municipality_code' => $attributes['MPIO_CDPMP'] ?? ($attributes['DPTO_CCDGO'].$attributes['MPIO_CCDGO']),
                    'department' => $attributes['DPTO_CNMBRE'],
                    'department_code' => $attributes['DPTO_CCDGO'],
                ];
            } catch (Throwable $e) {
                Log::warning('No fue posible consultar la ubicación DIVIPOLA del DANE', ['error' => $e->getMessage()]);

                return null;
            }
        });
    }
}
