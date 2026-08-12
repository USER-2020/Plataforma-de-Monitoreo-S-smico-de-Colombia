<?php

namespace Tests\Feature;

use App\Data\EarthquakeData;
use App\Models\Earthquake;
use App\Models\EarthquakeSubscriber;
use App\Notifications\NewEarthquakeAlert;
use App\Services\Earthquake\EarthquakeProviderInterface;
use App\Services\Earthquake\EarthquakeService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EarthquakeSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_normalizes_and_does_not_duplicate_events(): void
    {
        $provider = new class implements EarthquakeProviderInterface
        {
            public function name(): string
            {
                return 'test';
            }

            public function latest(): array
            {
                return [new EarthquakeData('same', 4.1, 'ml', 6.2, -73.1, 148, 'Los Santos', 'Los Santos', 'Santander', CarbonImmutable::now(), 'test', null, ['ok' => true])];
            }
        };
        $service = new EarthquakeService($provider);
        $this->assertSame(1, $service->sync()['created']);
        $this->assertSame(0, $service->sync()['created']);
        $this->assertDatabaseCount('earthquakes', 1);
        $this->assertDatabaseCount('earthquake_source_reports', 1);
        $this->assertSame(4.1, Earthquake::first()->magnitude);
    }

    public function test_sync_notifies_matching_subscribers(): void
    {
        Notification::fake();
        $subscriber = EarthquakeSubscriber::create(['name' => 'Ana', 'email' => 'ana@example.com', 'min_magnitude' => 4, 'department' => 'Santander', 'is_active' => true, 'subscribed_at' => now()]);
        EarthquakeSubscriber::create(['name' => 'Ana', 'email' => 'ana@example.com', 'min_magnitude' => 3, 'department' => null, 'is_active' => true, 'subscribed_at' => now()]);
        $provider = new class implements EarthquakeProviderInterface
        {
            public function name(): string
            {
                return 'test';
            }

            public function latest(): array
            {
                return [new EarthquakeData('alert', 4.8, 'ml', 6.2, -73.1, 148, 'Los Santos', 'Los Santos', 'Santander', CarbonImmutable::now(), 'test', null, [])];
            }
        };

        (new EarthquakeService($provider))->sync();

        Notification::assertSentTo($subscriber, NewEarthquakeAlert::class);
        Notification::assertSentTimes(NewEarthquakeAlert::class, 1);
    }

    public function test_reports_from_different_sources_are_merged_and_alert_only_once(): void
    {
        Notification::fake();
        $subscriber = EarthquakeSubscriber::create(['name' => 'Ana', 'email' => 'ana@example.com', 'min_magnitude' => 3, 'department' => null, 'is_active' => true, 'subscribed_at' => now()]);
        $occurredAt = CarbonImmutable::now('UTC');
        $provider = new class($occurredAt) implements EarthquakeProviderInterface
        {
            public function __construct(private CarbonImmutable $occurredAt) {}

            public function name(): string
            {
                return 'multi';
            }

            public function latest(): array
            {
                return [
                    new EarthquakeData('sgc-1', 4.5, 'ml', 6.20, -73.10, 145, 'Santander', null, 'Santander', $this->occurredAt, 'sgc', null, []),
                    new EarthquakeData('usgs-1', 4.6, 'mww', 6.22, -73.12, 148, 'Santander', null, 'Santander', $this->occurredAt->addSeconds(20), 'usgs', null, []),
                ];
            }
        };

        $result = (new EarthquakeService($provider))->sync();

        $this->assertSame(1, $result['created']);
        $this->assertDatabaseCount('earthquakes', 1);
        $this->assertDatabaseCount('earthquake_source_reports', 2);
        $this->assertSame('sgc', Earthquake::first()->source);
        Notification::assertSentToTimes($subscriber, NewEarthquakeAlert::class, 1);
    }
}
