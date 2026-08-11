<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEmscEarthquake;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ratchet\Client\WebSocket;
use React\EventLoop\Loop;
use Throwable;

use function Ratchet\Client\connect;

class ListenEmscEarthquakes extends Command
{
    protected $signature = 'earthquakes:listen-emsc';

    protected $description = 'Escucha el WebSocket de EMSC y publica sismos de Colombia en tiempo real';

    public function handle(): int
    {
        $url = config('earthquakes.emsc_websocket_url');
        $connect = function () use (&$connect, $url): void {
            $this->components->info('Conectando con EMSC '.$url);
            connect($url)->then(function (WebSocket $socket) use (&$connect): void {
                $this->components->info('EMSC conectado; esperando eventos de Colombia.');
                $socket->on('message', function ($message): void {
                    try {
                        ProcessEmscEarthquake::dispatch((string) $message);
                        $this->line('Evento EMSC enviado a la cola.');
                    } catch (Throwable $e) {
                        Log::warning('Mensaje EMSC inválido', ['error' => $e->getMessage()]);
                    }
                });
                $socket->on('close', function () use (&$connect): void {
                    $this->components->warn('EMSC desconectado; reintentando en 5 segundos.');
                    Loop::addTimer(5, $connect);
                });
            }, function (Throwable $e) use (&$connect): void {
                Log::error('No fue posible conectar con EMSC', ['error' => $e->getMessage()]);
                Loop::addTimer(5, $connect);
            });
        };
        $connect();
        Loop::run();

        return self::SUCCESS;
    }
}
