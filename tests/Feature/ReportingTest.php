<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_reports_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Reports/Index')
                ->has('properties')
        );
    }

    public function test_can_fetch_report_data()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id, 'price_per_night' => 100]);

        // Create a booking for 5 nights: 2025-01-01 to 2025-01-06
        Booking::factory()->create([
            'property_id' => $property->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-06',
            'total_price' => 500,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->get(route('reports.data', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'property_id' => $property->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'total_revenue' => 500,
            'total_bookings' => 1,
        ]);

        // Occupancy: 5 nights booked / 31 nights total = 16.1%
        $response->assertJsonPath('occupancy_rate', 16.1);
    }

    public function test_occupancy_calculation_with_multiple_properties()
    {
        $user = User::factory()->create();
        $property1 = Property::factory()->create(['user_id' => $user->id]);
        $property2 = Property::factory()->create(['user_id' => $user->id]);

        // Property 1: 10 nights booked
        Booking::factory()->create([
            'property_id' => $property1->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-11',
            'status' => 'confirmed',
        ]);

        // Property 2: 0 nights booked

        // Total available nights in Jan: 31 * 2 = 62
        // Booked nights: 10
        // Occupancy: 10 / 62 = 16.1%

        $response = $this->actingAs($user)->get(route('reports.data', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            // No property_id filter
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('occupancy_rate', 16.1);
    }
}
