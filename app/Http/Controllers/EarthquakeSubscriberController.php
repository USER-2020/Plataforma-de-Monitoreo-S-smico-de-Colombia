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
        $validated = $request->validated();
        $preferenceKey = EarthquakeSubscriber::preferenceKey(
            $validated['email'],
            (float) $validated['min_magnitude'],
            $validated['department'] ?? null,
        );

        if (EarthquakeSubscriber::where('preference_key', $preferenceKey)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Este correo ya tiene una alerta con la misma magnitud y el mismo departamento.',
            ]);
        }

        $subscriber = EarthquakeSubscriber::create([
            ...$validated,
            'preference_key' => $preferenceKey,
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        $subscriber->notifyNow(new WelcomeEarthquakeAlerts);

        return back()->with('success', '¡Listo! Tu nueva alerta quedó activada.');
    }
}
