<?php

namespace App\Providers;

use App\Services\Earthquake\EarthquakeProviderInterface;
use App\Services\Earthquake\EarthquakeService;
use App\Services\Earthquake\EmscEarthquakeProvider;
use App\Services\Earthquake\SgcEarthquakeProvider;
use App\Services\Earthquake\UsgsEarthquakeProvider;
use App\Services\Geography\DaneLocationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EarthquakeProviderInterface::class, fn () => match (config('earthquakes.provider')) {
            'sgc' => app(SgcEarthquakeProvider::class), 'emsc' => app(EmscEarthquakeProvider::class), default => app(UsgsEarthquakeProvider::class),
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
        //
    }
}
