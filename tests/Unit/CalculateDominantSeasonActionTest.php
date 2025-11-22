<?php

namespace Tests\Unit;

use App\Actions\Booking\CalculateDominantSeasonAction;
use App\Models\Property;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateDominantSeasonActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_dominant_season_correctly()
    {
        $property = Property::factory()->create();
        $defaultSeason = Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => true,
            'name' => 'Default',
        ]);

        $summerSeason = Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => false,
            'name' => 'Summer',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'priority' => 10,
        ]);

        $action = new CalculateDominantSeasonAction();

        // Case 1: Mostly default season
        $dominant = $action->execute(now(), now()->addDays(5));
        $this->assertTrue($dominant->is($defaultSeason));

        // Case 2: Mostly summer season
        $dominant = $action->execute(now()->addDays(10), now()->addDays(15));
        $this->assertTrue($dominant->is($summerSeason));
    }

    public function test_handles_null_dates_gracefully()
    {
        $property = Property::factory()->create();
        $defaultSeason = Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => true,
            'start_date' => null,
            'end_date' => null,
        ]);

        $action = new CalculateDominantSeasonAction();

        // Should not throw exception
        $dominant = $action->execute(now(), now()->addDays(5));

        $this->assertNotNull($dominant);
        $this->assertTrue($dominant->is($defaultSeason));
    }
}
