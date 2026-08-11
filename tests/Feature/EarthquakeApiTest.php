<?php

namespace Tests\Feature;

use App\Models\Earthquake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarthquakeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_filters_by_magnitude_and_department(): void
    {
        Earthquake::factory()->create(['magnitude' => 4.2, 'department' => 'Santander']);
        Earthquake::factory()->create(['magnitude' => 2.1, 'department' => 'Caldas']);
        $this->getJson('/api/earthquakes?min_magnitude=4&department=Santander')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.department', 'Santander');
    }
}
