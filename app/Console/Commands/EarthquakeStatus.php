<?php

namespace App\Console\Commands;

use App\Models\Earthquake;
use App\Models\EarthquakeSourceReport;
use App\Models\EarthquakeSyncLog;
use Illuminate\Console\Command;
use Throwable;

class EarthquakeStatus extends Command
{
    protected $signature = 'earthquakes:status';

    protected $description = 'Muestra el estado de actualización y las fuentes sísmicas';

    public function handle(): int
    {
        try {
            $latest = Earthquake::latest('occurred_at')->first();
            $sync = EarthquakeSyncLog::latest()->first();
            $sources = EarthquakeSourceReport::query()
                ->where('occurred_at', '>=', now()->subDay())
                ->selectRaw('provider, COUNT(*) total, MAX(occurred_at) latest')
                ->groupBy('provider')->orderBy('provider')->get();

            $this->table(['Dato', 'Valor'], [
                ['Proveedor configurado', config('earthquakes.provider')],
                ['Hora del servidor', now()->toDateTimeString().' '.config('app.timezone')],
                ['Último sismo', $latest?->occurred_at?->toDateTimeString() ?? 'Sin registros'],
                ['Antigüedad del último sismo', $latest?->occurred_at?->diffForHumans() ?? 'No disponible'],
                ['Última ejecución', $sync?->started_at?->toDateTimeString() ?? 'Nunca'],
                ['Estado de sincronización', $sync?->status ?? 'Sin información'],
                ['Error', $sync?->error_message ?: 'Ninguno'],
            ]);
            $this->table(['Fuente', 'Reportes últimas 24 h', 'Último reporte'], $sources->map(fn ($row) => [
                strtoupper($row->provider), $row->total, $row->latest,
            ])->all());

            if (! $sync || $sync->started_at->lt(now()->subMinutes(3))) {
                $this->warn('El scheduler no registra una sincronización reciente. Revisa el cron y limpia sus bloqueos.');
                return self::FAILURE;
            }

            return $sync->status === 'success' ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('No fue posible consultar el estado: '.$exception->getMessage());
            return self::FAILURE;
        }
    }
}
