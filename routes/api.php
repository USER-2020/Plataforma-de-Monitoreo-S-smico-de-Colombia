<?php

use App\Http\Controllers\EarthquakeApiController;
use Illuminate\Support\Facades\Route;

Route::get('/earthquakes', EarthquakeApiController::class);
