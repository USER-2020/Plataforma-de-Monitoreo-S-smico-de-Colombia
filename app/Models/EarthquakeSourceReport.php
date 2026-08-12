<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarthquakeSourceReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'magnitude' => 'float', 'latitude' => 'float', 'longitude' => 'float',
        'depth_km' => 'float', 'occurred_at' => 'immutable_datetime', 'raw_data' => 'array',
    ];

    public function earthquake()
    {
        return $this->belongsTo(Earthquake::class);
    }
}
