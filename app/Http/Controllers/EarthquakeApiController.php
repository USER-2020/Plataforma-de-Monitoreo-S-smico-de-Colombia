<?php

namespace App\Http\Controllers;

use App\Services\Earthquake\EarthquakeService;
use Illuminate\Http\Request;

class EarthquakeApiController extends Controller
{
    public function __invoke(Request $request, EarthquakeService $service)
    {
        $data = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'min_magnitude' => 'nullable|numeric', 'max_magnitude' => 'nullable|numeric', 'department' => 'nullable|string|max:100', 'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180', 'radius' => 'nullable|numeric|min:1|max:2000']);

        return $service->filtered($data)->latest('occurred_at')->paginate(min((int) $request->input('per_page', 100), 500));
    }
}
