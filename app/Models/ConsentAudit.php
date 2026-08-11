<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentAudit extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['consents' => 'array', 'ip_address' => 'encrypted', 'consented_at' => 'datetime'];
    }
}
