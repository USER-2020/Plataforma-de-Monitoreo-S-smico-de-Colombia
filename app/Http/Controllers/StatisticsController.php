<?php

namespace App\Http\Controllers;

use App\Models\Earthquake;
use App\Services\Earthquake\EarthquakeService;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function __invoke(EarthquakeService $service)
    {
        return Inertia::render('Statistics/Index', ['statistics' => $service->statistics(), 'daily' => Earthquake::selectRaw('date(occurred_at) date, count(*) total')->groupBy('date')->orderBy('date')->limit(30)->get(), 'magnitudes' => Earthquake::selectRaw('floor(magnitude) bucket, count(*) total')->groupBy('bucket')->orderBy('bucket')->get()]);
    }
}
