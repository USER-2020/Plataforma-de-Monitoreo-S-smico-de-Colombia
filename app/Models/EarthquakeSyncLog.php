<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarthquakeSyncLog extends Model
{
    protected $guarded = [];

    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];
}
