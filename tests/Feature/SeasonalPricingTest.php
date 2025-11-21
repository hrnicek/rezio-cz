<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\SeasonalPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonalPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_seasonal_price()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('properties.seasonal-prices.store', $property), [
            'name' => 'Summer',
            'start_date' => '2025-06-01',
            'end_date' => '2025-08-31',
            'price_per_night' => 200,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('seasonal_prices', [
            'property_id' => $property->id,
            'name' => 'Summer',
            'price_per_night' => 200,
        ]);
    }

    public function test_cannot_create_overlapping_seasonal_price()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        SeasonalPrice::factory()->create([
            'property_id' => $property->id,
            'start_date' => '2025-06-01',
            'end_date' => '2025-08-31',
        ]);

        $response = $this->actingAs($user)->post(route('properties.seasonal-prices.store', $property), [
            'name' => 'Overlap',
            'start_date' => '2025-07-01',
            'end_date' => '2025-07-15',
            'price_per_night' => 300,
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_price_calculation_uses_seasonal_price()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user->id,
            'price_per_night' => 100,
            'widget_token' => 'test-token',
        ]);

        SeasonalPrice::factory()->create([
            'property_id' => $property->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30', // June is 200
            'price_per_night' => 200,
        ]);

        // Booking: May 30 to June 2 (3 nights)
        // May 30: 100 (Base)
        // May 31: 100 (Base)
        // June 1: 200 (Season)
        // Total: 400

        $response = $this->post(route('widget.store', 'test-token'), [
            'start_date' => '2026-05-30',
            'end_date' => '2026-06-02',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '123456789',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'property_id' => $property->id,
            'total_price' => 400,
        ]);
    }
}
