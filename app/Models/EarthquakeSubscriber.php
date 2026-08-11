<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class EarthquakeSubscriber extends Model
{
    use Notifiable;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (EarthquakeSubscriber $subscriber) {
            $subscriber->email = mb_strtolower(trim($subscriber->email));
            $subscriber->department = $subscriber->department ? trim($subscriber->department) : null;
            $subscriber->preference_key = static::preferenceKey(
                $subscriber->email,
                (float) $subscriber->min_magnitude,
                $subscriber->department,
            );
        });
    }

    public static function preferenceKey(string $email, float $magnitude, ?string $department): string
    {
        $magnitude = number_format($magnitude, 1, '.', '');
        $department = mb_strtolower(trim((string) $department));

        return hash('sha256', mb_strtolower(trim($email))."|{$magnitude}|{$department}");
    }

    protected function casts(): array
    {
        return ['min_magnitude' => 'float', 'is_active' => 'boolean', 'subscribed_at' => 'datetime'];
    }
}
