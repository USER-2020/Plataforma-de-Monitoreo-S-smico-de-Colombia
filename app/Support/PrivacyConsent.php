<?php

namespace App\Support;

use Illuminate\Http\Request;

class PrivacyConsent
{
    public const COOKIE = 'terracosismos_consent';

    public static function choices(Request $request): array
    {
        $value = $request->cookie(self::COOKIE);
        $decoded = is_string($value) ? json_decode(urldecode($value), true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    public static function allows(Request $request, string $service): bool
    {
        return self::choices($request)[$service] ?? false;
    }

    public static function hash(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }
}
