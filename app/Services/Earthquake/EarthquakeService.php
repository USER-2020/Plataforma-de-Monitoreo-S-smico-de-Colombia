<?php

namespace App\Services\Earthquake;

use App\Models\Earthquake;
use App\Models\EarthquakeSubscriber;
use App\Models\EarthquakeSyncLog;
use App\Notifications\NewEarthquakeAlert;
use App\Services\Geography\DaneLocationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class EarthquakeService
{
    public function __construct(private EarthquakeProviderInterface $provider, private ?DaneLocationService $dane = null) {}

    public function sync(): array
    {
        $log = EarthquakeSyncLog::create(['provider' => $this->provider->name(), 'started_at' => now(), 'status' => 'running']);
        $created = $updated = 0;
        $newEvents = collect();
        try {
            $events = $this->provider->latest();
            foreach ($events as $event) {
                $model = Earthquake::query()->where(['external_id' => $event->externalId, 'source' => $event->source])->first();
                $location = $this->dane?->locate($event->latitude, $event->longitude);
                Earthquake::updateOrCreate(['external_id' => $event->externalId, 'source' => $event->source], [...$event->toArray(), ...($location ?? [])]);
                if ($model) {
                    $updated++;
                } else {
                    $created++;
                    $newEvents->push(Earthquake::where(['external_id' => $event->externalId, 'source' => $event->source])->first());
                }
            }
            $newEvents->each(fn (Earthquake $earthquake) => $this->notifySubscribers($earthquake));
            $log->update(['finished_at' => now(), 'status' => 'success', 'events_received' => count($events), 'events_created' => $created, 'events_updated' => $updated]);
            Cache::flush();

            return compact('created', 'updated') + ['received' => count($events)];
        } catch (Throwable $e) {
            $log->update(['finished_at' => now(), 'status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error('Earthquake synchronization failed', ['provider' => $this->provider->name(), 'exception' => $e]);
            throw $e;
        }
    }

    public function filtered(array $filters = []): Builder
    {
        return Earthquake::query()->betweenDates($filters['from'] ?? null, $filters['to'] ?? null)->magnitude(isset($filters['min_magnitude']) ? (float) $filters['min_magnitude'] : null, isset($filters['max_magnitude']) ? (float) $filters['max_magnitude'] : null)->department($filters['department'] ?? null)
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($s) => $s->where('place', 'like', "%{$v}%")->orWhere('municipality', 'like', "%{$v}%")))
            ->when(isset($filters['latitude'],$filters['longitude'],$filters['radius']), fn ($q) => $this->withinRadius($q, (float) $filters['latitude'], (float) $filters['longitude'], (float) $filters['radius']));
    }

    public function recent(int $days = 7, int $limit = 100)
    {
        return Earthquake::recent($days)->latest('occurred_at')->limit($limit)->get();
    }

    public function statistics(): array
    {
        return Cache::remember('earthquakes.statistics', 300, function () {
            $day = now()->subDay();
            $week = now()->subDays(7);

            return ['last24h' => Earthquake::where('occurred_at', '>=', $day)->count(), 'last7d' => Earthquake::where('occurred_at', '>=', $week)->count(), 'max24h' => (float) Earthquake::where('occurred_at', '>=', $day)->max('magnitude'), 'max7d' => (float) Earthquake::where('occurred_at', '>=', $week)->max('magnitude'), 'averageDepth' => round((float) Earthquake::avg('depth_km'), 1), 'averageMagnitude' => round((float) Earthquake::avg('magnitude'), 2), 'latest' => Earthquake::latest('occurred_at')->first(), 'departments' => Earthquake::selectRaw('department, count(*) total')->whereNotNull('department')->groupBy('department')->orderByDesc('total')->limit(10)->get()];
        });
    }

    public function nearby(Earthquake $earthquake, float $radius = 150)
    {
        return $this->withinRadius(Earthquake::whereKeyNot($earthquake->id), $earthquake->latitude, $earthquake->longitude, $radius)->limit(10)->get();
    }

    private function withinRadius(Builder $query, float $lat, float $lng, float $radius): Builder
    {
        return $query->selectRaw('earthquakes.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude)-radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance_km', [$lat, $lng, $lat])->having('distance_km', '<=', $radius)->orderBy('distance_km');
    }

    public function notifySubscribers(Earthquake $earthquake): void
    {
        EarthquakeSubscriber::query()->where('is_active', true)
            ->where('min_magnitude', '<=', $earthquake->magnitude)
            ->where(fn ($query) => $query->whereNull('department')->orWhere('department', $earthquake->department))
            ->each(fn (EarthquakeSubscriber $subscriber) => $subscriber->notify(new NewEarthquakeAlert($earthquake)));
    }
}
