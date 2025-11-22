<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Models\Season;
use App\Services\SeasonalPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonalPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_price_using_default_season()
    {
        $property = Property::factory()->create(['price_per_night' => 100]);
        Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => true,
            'price' => 150,
        ]);

        $service = new SeasonalPricingService();
        $price = $service->calculate_stay_price(
            $property->id,
            now()->addDays(1),
            now()->addDays(3) // 2 nights
        );

        $this->assertEquals(300, $price); // 150 * 2
    }

    public function test_calculates_price_using_specific_season()
    {
        $property = Property::factory()->create(['price_per_night' => 100]);
        Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => true,
            'price' => 100,
        ]);

        $start = now()->addDays(10);
        $end = now()->addDays(12); // 2 nights

        Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => false,
            'start_date' => $start,
            'end_date' => $end,
            'price' => 200,
        ]);

        $service = new SeasonalPricingService();
        $price = $service->calculate_stay_price(
            $property->id,
            $start,
            $end
        );

        $this->assertEquals(400, $price); // 200 * 2
    }

    public function test_calculates_price_mixed_seasons_with_priority()
    {
        $property = Property::factory()->create(['price_per_night' => 100]);
        Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => true,
            'price' => 100,
        ]);

        $start = now()->addDays(10);
        $end = now()->addDays(12); // 2 nights

        // Lower priority season
        Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => false,
            'start_date' => $start,
            'end_date' => $end,
            'price' => 200,
            'priority' => 10,
        ]);

        // Higher priority season (should win)
        Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => false,
            'start_date' => $start,
            'end_date' => $end,
            'price' => 300,
            'priority' => 20,
        ]);

        $service = new SeasonalPricingService();
        $price = $service->calculate_stay_price(
            $property->id,
            $start,
            $end
        );

        $this->assertEquals(600, $price); // 300 * 2
    }

    public function test_calculates_price_recurring_season()
    {
        $property = Property::factory()->create(['price_per_night' => 100]);

        // Recurring season for current month
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        Season::factory()->create([
            'property_id' => $property->id,
            'is_default' => false,
            'is_recurring' => true,
            'start_date' => $currentMonthStart, // Year is ignored in logic
            'end_date' => $currentMonthEnd,
            'price' => 200,
        ]);

        $service = new SeasonalPricingService();
        $price = $service->calculate_stay_price(
            $property->id,
            now()->addDays(1),
            now()->addDays(3) // 2 nights
        );

        $this->assertEquals(400, $price); // 200 * 2
    }
}
