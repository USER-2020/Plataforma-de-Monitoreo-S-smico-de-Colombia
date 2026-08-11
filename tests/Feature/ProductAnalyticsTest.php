<?php

namespace Tests\Feature;

use App\Models\EarthquakeSubscriber;
use App\Models\NotificationDelivery;
use App\Models\SiteVisitDay;
use App\Notifications\WelcomeEarthquakeAlerts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSent;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_views_are_counted_without_storing_personal_data(): void
    {
        $consent = urlencode(json_encode(['essential' => true, 'analyticsAudit' => true]));
        $this->withCookie('terracosismos_visitor', 'stable-anonymous-browser')->withUnencryptedCookie('terracosismos_consent', $consent)->get('/')->assertOk();
        $this->withCookie('terracosismos_visitor', 'stable-anonymous-browser')->withUnencryptedCookie('terracosismos_consent', $consent)->get('/')->assertOk();

        $this->assertDatabaseCount('site_visit_days', 1);
        $visit = SiteVisitDay::firstOrFail();
        $this->assertSame(2, $visit->page_views);
        $this->assertSame(64, strlen($visit->visitor_hash));
    }

    public function test_visits_are_not_tracked_without_analytics_consent(): void
    {
        $consent = urlencode(json_encode(['essential' => true, 'analyticsAudit' => false]));

        $this->withUnencryptedCookie('terracosismos_consent', $consent)->get('/')->assertOk();

        $this->assertDatabaseCount('site_visit_days', 0);
    }

    public function test_consent_and_actions_are_audited_with_encrypted_ip(): void
    {
        $this->assertDatabaseCount('system_cookies', 5);
        $consents = ['essential' => true, 'analyticsAudit' => true];
        $this->postJson('/privacidad/consentimiento', [
            'config_version' => '2026-08-11.1', 'consents' => $consents, 'action' => 'save',
        ])->assertOk()->assertCookie('terracosismos_audit');

        $consentCookie = urlencode(json_encode($consents));
        $this->withCredentials()->withUnencryptedCookie('terracosismos_consent', $consentCookie)->postJson('/privacidad/eventos', [
            'action' => 'click', 'path' => '/', 'metadata' => ['label' => 'Monitor'],
        ])->assertCreated();

        $this->assertDatabaseCount('consent_audits', 1);
        $this->assertDatabaseCount('user_action_audits', 1);
        $this->assertNotSame('127.0.0.1', \DB::table('consent_audits')->value('ip_address'));
    }

    public function test_successful_mail_notifications_are_recorded(): void
    {
        $subscriber = new EarthquakeSubscriber(['email' => 'ana@example.com']);

        event(new NotificationSent($subscriber, new WelcomeEarthquakeAlerts, 'mail'));

        $this->assertDatabaseHas('notification_deliveries', [
            'notification_type' => 'WelcomeEarthquakeAlerts',
            'channel' => 'mail',
            'status' => 'sent',
        ]);
        $this->assertNotSame('ana@example.com', NotificationDelivery::firstOrFail()->recipient_hash);
    }

    public function test_statistics_page_exposes_product_metrics(): void
    {
        SiteVisitDay::create(['visitor_hash' => str_repeat('a', 64), 'visited_on' => today(), 'page_views' => 5]);
        EarthquakeSubscriber::create(['name' => 'Ana', 'email' => 'ana@example.com', 'min_magnitude' => 4, 'department' => 'Caldas', 'is_active' => true, 'subscribed_at' => now()]);
        EarthquakeSubscriber::create(['name' => 'Ana', 'email' => 'ana@example.com', 'min_magnitude' => 5, 'department' => 'Caldas', 'is_active' => true, 'subscribed_at' => now()]);

        $this->get('/estadisticas')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Statistics/Index')
            ->where('system.uniqueVisitors', 1)
            ->where('system.pageViews', 5)
            ->where('system.subscribers', 1)
            ->where('system.activeAlerts', 2)
            ->has('trafficDaily', 1)
        );
    }
}
