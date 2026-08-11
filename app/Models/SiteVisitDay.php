<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisitDay extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['visited_on' => 'date'];
    }
}
