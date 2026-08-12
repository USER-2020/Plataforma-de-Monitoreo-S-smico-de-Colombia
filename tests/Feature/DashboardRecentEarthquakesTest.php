<?php

namespace Tests\Feature;

use App\Models\Earthquake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardRecentEarthquakesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_only_shows_earthquakes_from_the_configured_recent_window(): void
    {
        config(['earthquakes.recent_hours' => 24]);

        $recent = Earthquake::factory()->create(['occurred_at' => now()->subHours(23)]);
        $old = Earthquake::factory()->create(['occurred_at' => now()->subHours(25)]);

        $this->withoutVite();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->has('earthquakes', 1)
                ->where('earthquakes.0.id', $recent->id));

        $this->get('/sismos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Earthquakes/Index')
                ->has('earthquakes.data', 2)
                ->where('earthquakes.data.1.id', $old->id));
    }
}
