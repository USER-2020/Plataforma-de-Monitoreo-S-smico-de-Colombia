<?php

namespace App\Http\Controllers;

use App\Services\Earthquake\EarthquakeService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(EarthquakeService $service)
    {
        return Inertia::render('Dashboard/Index', ['earthquakes' => $service->recent(), 'statistics' => $service->statistics(), 'departments' => config('earthquakes.departments'), 'mapsKey' => config('services.google_maps.key')]);
    }
}
