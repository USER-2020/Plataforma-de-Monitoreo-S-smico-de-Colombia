<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActionAudit extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'ip_address' => 'encrypted', 'occurred_at' => 'datetime'];
    }
}
