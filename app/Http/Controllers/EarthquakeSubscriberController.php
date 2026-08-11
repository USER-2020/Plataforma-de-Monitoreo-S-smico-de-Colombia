<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEarthquakeSubscriberRequest;
use App\Models\EarthquakeSubscriber;
use App\Notifications\WelcomeEarthquakeAlerts;
use Illuminate\Validation\ValidationException;

class EarthquakeSubscriberController extends Controller
{
    public function store(StoreEarthquakeSubscriberRequest $request)
    {
        if (EarthquakeSubscriber::where('email', $request->validated('email'))->exists()) {
            throw ValidationException::withMessages(['email' => 'Este correo ya está registrado para recibir alertas.']);
        }

        $subscriber = EarthquakeSubscriber::create([
            ...$request->validated(), 'is_active' => true, 'subscribed_at' => now(),
        ]);
        $subscriber->notifyNow(new WelcomeEarthquakeAlerts);

        return back()->with('success', '¡Listo! Tus preferencias de alerta quedaron activadas.');
    }
}
