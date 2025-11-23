<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class ReportingTest extends TenantTestCase
{
    public function test_can_access_reports_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Admin/Reports/Index')
                ->has('properties')
        );
    }

    public function test_can_fetch_report_data()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['price_per_night' => 100]);

        // Create a booking for 5 nights: 2025-01-01 to 2025-01-06
        $booking = Booking::factory()->create([ // Assigned to booking
            'property_id' => $property->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-06',
            'total_price' => 500,
            // 'status' => 'confirmed', // Removed
        ]);
        $booking->update(["status" => 'confirmed']); // Set status using new method

        $response = $this->actingAs($user)->get(route('admin.reports.data', [
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
        $property1 = Property::factory()->create();
        $property2 = Property::factory()->create();

        // Property 1: 10 nights booked
        $booking1 = Booking::factory()->create([ // Assigned to booking1
            'property_id' => $property1->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-11',
            // 'status' => 'confirmed', // Removed
        ]);
        $booking1->update(["status" => 'confirmed']); // Set status using new method

        // Property 2: 0 nights booked

        // Total available nights in Jan: 31 * 2 = 62
        // Booked nights: 10
        // Occupancy: 10 / 62 = 16.1%

        $response = $this->actingAs($user)->get(route('admin.reports.data', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            // No property_id filter
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('occupancy_rate', 16.1);
    }
}
