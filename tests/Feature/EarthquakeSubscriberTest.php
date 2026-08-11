<?php

namespace Tests\Feature;

use App\Models\EarthquakeSubscriber;
use App\Notifications\WelcomeEarthquakeAlerts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EarthquakeSubscriberTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register_alert_preferences(): void
    {
        Notification::fake();

        $this->from('/')->post('/alertas/suscribir', [
            'name' => 'Ana', 'email' => 'ana@example.com',
            'min_magnitude' => 4, 'department' => 'Santander',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('earthquake_subscribers', [
            'email' => 'ana@example.com', 'department' => 'Santander', 'is_active' => true,
        ]);

        Notification::assertSentTo(
            EarthquakeSubscriber::where('email', 'ana@example.com')->firstOrFail(),
            WelcomeEarthquakeAlerts::class,
        );
    }

    public function test_a_visitor_can_subscribe_to_all_magnitudes(): void
    {
        Notification::fake();

        $this->post('/alertas/suscribir', [
            'name' => 'Luis', 'email' => 'luis@example.com',
            'min_magnitude' => 0, 'department' => null,
        ])->assertRedirect();

        $this->assertDatabaseHas('earthquake_subscribers', [
            'email' => 'luis@example.com', 'min_magnitude' => 0,
        ]);
    }

    public function test_an_email_address_cannot_be_registered_twice(): void
    {
        Notification::fake();
        EarthquakeSubscriber::create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'min_magnitude' => 4,
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        $this->from('/')->post('/alertas/suscribir', [
            'name' => 'Ana otra vez',
            'email' => 'ana@example.com',
            'min_magnitude' => 2,
            'department' => null,
        ])->assertRedirect('/')->assertSessionHasErrors([
            'email' => 'Este correo ya está registrado para recibir alertas.',
        ]);

        $this->assertDatabaseCount('earthquake_subscribers', 1);
        Notification::assertNothingSent();
    }

    public function test_the_welcome_email_uses_the_terracosismos_brand(): void
    {
        $subscriber = new EarthquakeSubscriber([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'min_magnitude' => 0,
        ]);

        $html = (new WelcomeEarthquakeAlerts)->toMail($subscriber)->render()->toHtml();

        $this->assertStringContainsString('terracosismos', $html);
        $this->assertStringContainsString('#861bc1', strtolower($html));
        $this->assertStringContainsString('https://terracosismos.online', $html);
        $this->assertStringContainsString('https://terracosismos.online/icons/pwa-192.png', $html);
        $this->assertStringContainsString('Monitoreo sísmico de Colombia', $html);
    }
}
