<?php

namespace App\Http\Controllers;

use App\Models\EarthquakeSyncLog;
use App\Services\Earthquake\EarthquakeService;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Index', ['logs' => EarthquakeSyncLog::latest()->limit(50)->get(), 'provider' => config('earthquakes.provider')]);
    }

    public function sync(EarthquakeService $service)
    {
        try {
            $result = $service->sync();

            return back()->with('success', "Sincronización completa: {$result['created']} nuevos");
        } catch (\Throwable $e) {
            return back()->with('error', 'El proveedor no respondió; se conservaron los datos existentes.');
        }
    }
}
