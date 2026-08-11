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
}
