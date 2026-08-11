<?php

namespace App\Http\Controllers;

use App\Models\ConsentAudit;
use App\Models\UserActionAudit;
use App\Support\PrivacyConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrivacyAuditController extends Controller
{
    public function consent(Request $request)
    {
        $validated = $request->validate([
            'config_version' => ['required', 'string', 'max:30'],
            'consents' => ['required', 'array'],
            'consents.analyticsAudit' => ['required', 'boolean'],
            'action' => ['required', 'in:save,accept_all,decline_all,update'],
        ]);
        $auditId = $request->cookie('terracosismos_audit') ?: (string) Str::uuid();
        $analyticsAllowed = (bool) $validated['consents']['analyticsAudit'];
        $ip = (string) $request->ip();

        ConsentAudit::create([
            'visitor_hash' => PrivacyConsent::hash($auditId),
            'ip_hash' => PrivacyConsent::hash($ip),
            'ip_address' => $analyticsAllowed ? $ip : null,
            'config_version' => $validated['config_version'],
            'consents' => $validated['consents'],
            'action' => $validated['action'],
            'path' => mb_substr((string) $request->header('Referer'), 0, 255),
            'consented_at' => now(),
        ]);

        return response()->json(['recorded' => true])->withCookie(
            cookie('terracosismos_audit', $auditId, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'Lax')
        );
    }

    public function action(Request $request)
    {
        abort_unless(PrivacyConsent::allows($request, 'analyticsAudit'), 403);
        $validated = $request->validate([
            'action' => ['required', 'string', 'max:80'],
            'path' => ['required', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);
        $auditId = $request->cookie('terracosismos_audit') ?: 'anonymous';
        $ip = (string) $request->ip();

        UserActionAudit::create([
            ...$validated,
            'metadata' => array_slice($validated['metadata'] ?? [], 0, 10),
            'visitor_hash' => PrivacyConsent::hash($auditId),
            'session_hash' => PrivacyConsent::hash($request->session()->getId()),
            'ip_hash' => PrivacyConsent::hash($ip),
            'ip_address' => $ip,
            'occurred_at' => now(),
        ]);

        return response()->json(['recorded' => true], 201);
    }
}
