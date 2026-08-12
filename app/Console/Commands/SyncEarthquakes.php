<?php

namespace App\Console\Commands;

use App\Services\Earthquake\EarthquakeService;
use Illuminate\Console\Command;

class SyncEarthquakes extends Command
{
    protected $signature = 'earthquakes:sync';

    protected $description = 'Sincroniza los eventos sísmicos del proveedor configurado';

    public function handle(EarthquakeService $service): int
    {
        try {
            $r = $service->sync();
            $this->info("Recibidos: {$r['received']}; nuevos: {$r['created']}; actualizados: {$r['updated']}");
            $this->table(
                ['Fuente', 'Estado', 'Recibidos', 'Error'],
                collect($r['providers'])->map(fn ($result, $source) => [
                    strtoupper($source),
                    $result['status'],
                    $result['received'],
                    $result['error'] ?: 'Ninguno',
                ])->values()->all(),
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
