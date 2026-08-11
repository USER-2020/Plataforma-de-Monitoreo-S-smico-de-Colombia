<?php

namespace App\Http\Controllers;

use App\Models\Earthquake;
use App\Services\Earthquake\EarthquakeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EarthquakeController extends Controller
{
    public function index(Request $request, EarthquakeService $service)
    {
        $filters = $request->only(['from', 'to', 'min_magnitude', 'max_magnitude', 'department', 'search']);

        return Inertia::render('Earthquakes/Index', ['earthquakes' => $service->filtered($filters)->latest('occurred_at')->paginate(20)->withQueryString(), 'filters' => $filters, 'departments' => Earthquake::whereNotNull('department')->distinct()->orderBy('department')->pluck('department')]);
    }

    public function show(Earthquake $earthquake, EarthquakeService $service)
    {
        return Inertia::render('Earthquakes/Show', ['earthquake' => $earthquake, 'nearby' => $service->nearby($earthquake, config('earthquakes.nearby_radius_km')), 'mapsKey' => config('services.google_maps.key')]);
    }
}
