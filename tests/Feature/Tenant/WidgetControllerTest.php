<?php

namespace Tests\Feature\Tenant;

use App\Models\Property;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class WidgetControllerTest extends TenantTestCase
{
    use RefreshDatabase;

    public function test_index_returns_season_information()
    {
        $property = Property::factory()->create();
        
        $season = Season::factory()->create([
            'property_id' => $property->id,
            'name' => 'Summer Season',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'priority' => 10,
            'price' => 1000,
            'is_recurring' => false,
            'is_fixed_range' => true,
        ]);

        $response = $this->getJson(route('api.widgets.index', [
            'id' => $property->id,
            'month' => now()->month,
            'year' => now()->year,
        ]));

        $response->assertOk();
        
        $response->assertJsonStructure([
            'days' => [
                '*' => [
                    'date',
                    'available',
                    'blackout',
                    'price',
                    'season' => [
                        'id',
                        'name',
                    ],
                ],
            ],
        ]);

        // Find a day within the season
        $dayInSeason = collect($response->json('days'))->first(function ($day) use ($season) {
            return $day['season'] && $day['season']['id'] === $season->id;
        });

        $this->assertNotNull($dayInSeason, 'No day found with the expected season.');
        $this->assertEquals('Summer Season', $dayInSeason['season']['name']);
    }

    public function test_index_returns_null_season_when_no_season_matches()
    {
        $property = Property::factory()->create();
        
        // No seasons created

        $response = $this->getJson(route('api.widgets.index', [
            'id' => $property->id,
            'month' => now()->month,
            'year' => now()->year,
        ]));

        $response->assertOk();

        $day = $response->json('days.0');
        $this->assertNull($day['season']);
    }
}
