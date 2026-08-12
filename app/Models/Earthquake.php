<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earthquake extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['magnitude' => 'float', 'latitude' => 'float', 'longitude' => 'float', 'depth_km' => 'float', 'occurred_at' => 'immutable_datetime', 'raw_data' => 'array'];

    public function sourceReports()
    {
        return $this->hasMany(EarthquakeSourceReport::class);
    }

    public function scopeMagnitude(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        return $query->when($min !== null, fn ($q) => $q->where('magnitude', '>=', $min))->when($max !== null, fn ($q) => $q->where('magnitude', '<=', $max));
    }

    public function scopeDepartment(Builder $query, ?string $department): Builder
    {
        return $query->when($department, fn ($q) => $q->where('department', $department));
    }

    public function scopeBetweenDates(Builder $query, mixed $from, mixed $to): Builder
    {
        return $query->when($from, fn ($q) => $q->where('occurred_at', '>=', $from))->when($to, fn ($q) => $q->where('occurred_at', '<=', $to));
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('occurred_at', '>=', now()->subDays($days));
    }
}
