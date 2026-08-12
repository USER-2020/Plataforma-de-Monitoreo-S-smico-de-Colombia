<?php

namespace App\Services\Earthquake;

use App\Data\EarthquakeData;
use App\Models\Earthquake;
use App\Models\EarthquakeSourceReport;
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
                [$model, $wasCreated] = $this->ingest($event);
                if ($wasCreated) {
                    $created++;
                    $newEvents->push($model);
                } else {
                    $updated++;
                }
            }
            $newEvents->each(fn (Earthquake $earthquake) => $this->notifySubscribers($earthquake));
            $log->update(['finished_at' => now(), 'status' => 'success', 'events_received' => count($events), 'events_created' => $created, 'events_updated' => $updated]);
            Cache::forget('earthquakes.statistics');

            $sources = collect($events)->countBy(fn (EarthquakeData $event) => $event->source)->all();

            $providers = method_exists($this->provider, 'diagnostics')
                ? $this->provider->diagnostics()
                : [$this->provider->name() => ['status' => 'success', 'received' => count($events), 'error' => null]];

            return compact('created', 'updated', 'sources', 'providers') + ['received' => count($events)];
        } catch (Throwable $e) {
            $log->update(['finished_at' => now(), 'status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error('Earthquake synchronization failed', ['provider' => $this->provider->name(), 'exception' => $e]);
            throw $e;
        }
    }

    public function ingest(EarthquakeData $event): array
    {
        $report = EarthquakeSourceReport::query()->where([
            'provider' => $event->source, 'external_id' => $event->externalId,
        ])->first();
        $earthquake = $report?->earthquake ?: $this->findMatchingEvent($event);
        $created = false;

        if (! $earthquake) {
            $location = $this->dane?->locate($event->latitude, $event->longitude);
            $earthquake = Earthquake::create([...$event->toArray(), ...($location ?? [])]);
            $created = true;
        } elseif ($this->shouldPromote($earthquake->source, $event->source)) {
            $location = $this->dane?->locate($event->latitude, $event->longitude);
            $earthquake->update([...$event->toArray(), ...($location ?? [])]);
        }

        EarthquakeSourceReport::updateOrCreate(
            ['provider' => $event->source, 'external_id' => $event->externalId],
            ['earthquake_id' => $earthquake->id, 'magnitude' => $event->magnitude,
                'latitude' => $event->latitude, 'longitude' => $event->longitude,
                'depth_km' => $event->depth, 'occurred_at' => $event->occurredAt,
                'source_url' => $event->sourceUrl, 'raw_data' => $event->rawData],
        );

        return [$earthquake->fresh()->loadCount('sourceReports'), $created];
    }

    private function findMatchingEvent(EarthquakeData $event): ?Earthquake
    {
        $seconds = (int) config('earthquakes.deduplication.seconds', 90);
        $distance = (float) config('earthquakes.deduplication.distance_km', 50);
        $magnitudeDelta = (float) config('earthquakes.deduplication.magnitude_delta', 0.8);

        return Earthquake::query()
            ->whereBetween('occurred_at', [$event->occurredAt->subSeconds($seconds), $event->occurredAt->addSeconds($seconds)])
            ->whereBetween('magnitude', [$event->magnitude - $magnitudeDelta, $event->magnitude + $magnitudeDelta])
            ->get()
            ->sortBy(fn (Earthquake $candidate) => abs($candidate->occurred_at->diffInSeconds($event->occurredAt)))
            ->first(fn (Earthquake $candidate) => $this->distanceKm($candidate->latitude, $candidate->longitude, $event->latitude, $event->longitude) <= $distance);
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function shouldPromote(string $current, string $incoming): bool
    {
        $priority = config('earthquakes.source_priority', []);

        return ($priority[$incoming] ?? 99) <= ($priority[$current] ?? 99);
    }

    public function filtered(array $filters = []): Builder
    {
        return Earthquake::query()->betweenDates($filters['from'] ?? null, $filters['to'] ?? null)->magnitude(isset($filters['min_magnitude']) ? (float) $filters['min_magnitude'] : null, isset($filters['max_magnitude']) ? (float) $filters['max_magnitude'] : null)->department($filters['department'] ?? null)
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($s) => $s->where('place', 'like', "%{$v}%")->orWhere('municipality', 'like', "%{$v}%")))
            ->when(isset($filters['latitude'],$filters['longitude'],$filters['radius']), fn ($q) => $this->withinRadius($q, (float) $filters['latitude'], (float) $filters['longitude'], (float) $filters['radius']));
    }

    public function recent(?int $hours = null, int $limit = 100)
    {
        $hours ??= (int) config('earthquakes.recent_hours', 24);

        return Earthquake::query()
            ->where('occurred_at', '>=', now()->subHours($hours))
            ->withCount('sourceReports')
            ->latest('occurred_at')
            ->limit($limit)
            ->get();
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
        if ($earthquake->occurred_at->lt(now()->subMinutes(config('earthquakes.alert_max_age_minutes', 30)))) {
            Log::info('Sismo histórico almacenado sin generar alertas', ['earthquake_id' => $earthquake->id]);

            return;
        }

        EarthquakeSubscriber::query()->where('is_active', true)
            ->where('min_magnitude', '<=', $earthquake->magnitude)
            ->where(fn ($query) => $query->whereNull('department')->orWhere('department', $earthquake->department))
            ->get()
            ->unique(fn (EarthquakeSubscriber $subscriber) => mb_strtolower($subscriber->email))
            ->each(fn (EarthquakeSubscriber $subscriber) => $subscriber->notify(new NewEarthquakeAlert($earthquake)));
    }
}
