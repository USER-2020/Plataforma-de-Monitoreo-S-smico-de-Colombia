<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EarthquakeController;
use App\Http\Controllers\EarthquakeSubscriberController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/dashboard', DashboardController::class);
Route::get('/sismos', [EarthquakeController::class, 'index'])->name('earthquakes.index');
Route::get('/sismos/{earthquake}', [EarthquakeController::class, 'show'])->name('earthquakes.show');
Route::get('/estadisticas', StatisticsController::class)->name('statistics');
Route::post('/alertas/suscribir', [EarthquakeSubscriberController::class, 'store'])->name('alerts.subscribe');
Route::get('/sitemap.xml', function () {
    $urls = [
        ['location' => route('dashboard'), 'frequency' => 'hourly', 'priority' => '1.0'],
        ['location' => route('earthquakes.index'), 'frequency' => 'hourly', 'priority' => '0.9'],
        ['location' => route('statistics'), 'frequency' => 'daily', 'priority' => '0.8'],
    ];

    return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin');
    Route::post('/sync', [AdminController::class, 'sync'])->name('admin.sync');
});
