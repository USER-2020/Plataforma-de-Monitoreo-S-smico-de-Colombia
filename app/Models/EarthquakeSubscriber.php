<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class EarthquakeSubscriber extends Model
{
    use Notifiable;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['min_magnitude' => 'float', 'is_active' => 'boolean', 'subscribed_at' => 'datetime'];
    }
}
