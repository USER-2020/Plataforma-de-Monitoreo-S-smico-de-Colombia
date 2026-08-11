<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EarthquakeController;
use App\Http\Controllers\EarthquakeSubscriberController;
use App\Http\Controllers\PrivacyAuditController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/dashboard', DashboardController::class);
Route::get('/sismos', [EarthquakeController::class, 'index'])->name('earthquakes.index');
Route::get('/sismos/{earthquake}', [EarthquakeController::class, 'show'])->name('earthquakes.show');
Route::get('/estadisticas', StatisticsController::class)->name('statistics');
Route::post('/alertas/suscribir', [EarthquakeSubscriberController::class, 'store'])->name('alerts.subscribe');
Route::post('/privacidad/consentimiento', [PrivacyAuditController::class, 'consent'])->middleware('throttle:30,1')->name('privacy.consent');
Route::post('/privacidad/eventos', [PrivacyAuditController::class, 'action'])->middleware('throttle:120,1')->name('privacy.actions');
Route::get('/privacidad', fn () => Inertia::render('Privacy/Index', [
    'cookies' => DB::table('system_cookies')->orderByDesc('required')->orderBy('name')->get(),
]))->name('privacy');
Route::get('/sitemap.xml', function () {
    $urls = [
        ['location' => route('dashboard'), 'frequency' => 'hourly', 'priority' => '1.0'],
        ['location' => route('earthquakes.index'), 'frequency' => 'hourly', 'priority' => '0.9'],
        ['location' => route('statistics'), 'frequency' => 'daily', 'priority' => '0.8'],
        ['location' => route('privacy'), 'frequency' => 'monthly', 'priority' => '0.5'],
    ];

    return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin');
    Route::post('/sync', [AdminController::class, 'sync'])->name('admin.sync');
});
