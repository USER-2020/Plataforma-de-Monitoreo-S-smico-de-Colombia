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
            $this->table(['Fuente', 'Reportes'], collect($r['sources'])->map(fn ($total, $source) => [$source, $total])->values()->all());

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
