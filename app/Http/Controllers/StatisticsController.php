<?php

namespace App\Http\Controllers;

use App\Models\ConsentAudit;
use App\Models\Earthquake;
use App\Models\EarthquakeSubscriber;
use App\Models\NotificationDelivery;
use App\Models\SiteVisitDay;
use App\Models\UserActionAudit;
use App\Notifications\NewEarthquakeAlert;
use App\Notifications\WelcomeEarthquakeAlerts;
use App\Services\Earthquake\EarthquakeService;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function __invoke(EarthquakeService $service)
    {
        $since = today()->subDays(29);

        return Inertia::render('Statistics/Index', [
            'statistics' => $service->statistics(),
            'daily' => Earthquake::selectRaw('date(occurred_at) date, count(*) total')->groupBy('date')->orderBy('date')->limit(30)->get(),
            'magnitudes' => Earthquake::selectRaw('floor(magnitude) bucket, count(*) total')->groupBy('bucket')->orderBy('bucket')->get(),
            'system' => [
                'uniqueVisitors' => SiteVisitDay::distinct('visitor_hash')->count('visitor_hash'),
                'visitors7d' => SiteVisitDay::where('visited_on', '>=', today()->subDays(6))->distinct('visitor_hash')->count('visitor_hash'),
                'pageViews' => (int) SiteVisitDay::sum('page_views'),
                'subscribers' => EarthquakeSubscriber::distinct('email')->count('email'),
                'activeAlerts' => EarthquakeSubscriber::where('is_active', true)->count(),
                'emailsSent' => NotificationDelivery::where('status', 'sent')->count(),
                'earthquakeAlertsSent' => NotificationDelivery::where('status', 'sent')->where('notification_type', class_basename(NewEarthquakeAlert::class))->count(),
                'welcomeEmailsSent' => NotificationDelivery::where('status', 'sent')->where('notification_type', class_basename(WelcomeEarthquakeAlerts::class))->count(),
                'failedEmails' => NotificationDelivery::where('status', 'failed')->count(),
                'consentDecisions' => ConsentAudit::count(),
                'auditedActions' => UserActionAudit::count(),
            ],
            'trafficDaily' => SiteVisitDay::query()->where('visited_on', '>=', $since)
                ->selectRaw('visited_on date, sum(page_views) views, count(*) visitors')
                ->groupBy('visited_on')->orderBy('visited_on')->get(),
            'notificationsDaily' => NotificationDelivery::query()->where('status', 'sent')->where('created_at', '>=', $since)
                ->selectRaw('date(created_at) date, count(*) total')
                ->groupByRaw('date(created_at)')->orderBy('date')->get(),
        ]);
    }
}
