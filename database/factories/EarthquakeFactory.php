<?php

namespace Database\Factories;

use App\Models\Earthquake;
use Illuminate\Database\Eloquent\Factories\Factory;

class EarthquakeFactory extends Factory
{
    protected $model = Earthquake::class;

    public function definition(): array
    {
        return ['external_id' => fake()->uuid(), 'source' => 'usgs', 'magnitude' => fake()->randomFloat(1, 1, 6), 'magnitude_type' => 'ml', 'latitude' => fake()->latitude(0, 13), 'longitude' => fake()->longitude(-79, -66), 'depth_km' => fake()->randomFloat(1, 1, 180), 'place' => fake()->city().', Colombia', 'municipality' => fake()->city(), 'department' => fake()->randomElement(['Santander', 'Caldas', 'Antioquia']), 'country' => 'Colombia', 'occurred_at' => now(), 'source_url' => null, 'raw_data' => []];
    }
}
