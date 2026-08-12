<?php

namespace App\Providers;

use App\Models\NotificationDelivery;
use App\Notifications\NewEarthquakeAlert;
use App\Services\Earthquake\EarthquakeProviderInterface;
use App\Services\Earthquake\EarthquakeService;
use App\Services\Earthquake\EmscEarthquakeProvider;
use App\Services\Earthquake\GeofonEarthquakeProvider;
use App\Services\Earthquake\MultiEarthquakeProvider;
use App\Services\Earthquake\SgcEarthquakeProvider;
use App\Services\Earthquake\UsgsEarthquakeProvider;
use App\Services\Geography\DaneLocationService;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EarthquakeProviderInterface::class, fn () => match (config('earthquakes.provider')) {
            'sgc' => app(SgcEarthquakeProvider::class),
            'emsc' => app(EmscEarthquakeProvider::class),
            'usgs' => app(UsgsEarthquakeProvider::class),
            'geofon' => app(GeofonEarthquakeProvider::class),
            default => new MultiEarthquakeProvider([
                app(SgcEarthquakeProvider::class), app(EmscEarthquakeProvider::class),
                app(UsgsEarthquakeProvider::class), app(GeofonEarthquakeProvider::class),
            ]),
        });
        $this->app->bind(EarthquakeService::class, fn () => new EarthquakeService(
            app(EarthquakeProviderInterface::class), app(DaneLocationService::class)
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(NotificationSent::class, fn (NotificationSent $event) => $this->recordNotification($event, 'sent'));
        Event::listen(NotificationFailed::class, fn (NotificationFailed $event) => $this->recordNotification($event, 'failed'));
    }

    private function recordNotification(NotificationSent|NotificationFailed $event, string $status): void
    {
        if ($event->channel !== 'mail') {
            return;
        }

        NotificationDelivery::create([
            'notification_type' => class_basename($event->notification),
            'channel' => $event->channel,
            'recipient_hash' => isset($event->notifiable->email)
                ? hash_hmac('sha256', mb_strtolower($event->notifiable->email), (string) config('app.key'))
                : null,
            'earthquake_id' => $event->notification instanceof NewEarthquakeAlert ? $event->notification->earthquake->id : null,
            'status' => $status,
            'delivered_at' => $status === 'sent' ? now() : null,
        ]);
    }
}
