<?php

namespace App\Http\Middleware;

use App\Models\SiteVisitDay;
use App\Support\PrivacyConsent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackAnonymousVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || ! $response->isSuccessful() || $request->is('admin*', 'api/*', 'up') || ! PrivacyConsent::allows($request, 'analyticsAudit')) {
            return $response;
        }

        $visitorId = $request->cookie('terracosismos_visitor') ?: (string) Str::uuid();
        $visitorHash = hash_hmac('sha256', $visitorId, (string) config('app.key'));
        $visit = SiteVisitDay::firstOrCreate(
            ['visitor_hash' => $visitorHash, 'visited_on' => today()],
            ['page_views' => 0, 'last_path' => $request->path()],
        );
        $visit->increment('page_views', 1, ['last_path' => $request->path()]);

        if (! $request->hasCookie('terracosismos_visitor')) {
            $response->withCookie(cookie('terracosismos_visitor', $visitorId, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'Lax'));
        }

        return $response;
    }
}
