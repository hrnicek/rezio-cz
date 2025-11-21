<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSearchExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_bookings()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $booking1 = Booking::create([ // Assigned to booking1
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            // 'status' => 'confirmed', // Removed
            'total_price' => 100,
            'guest_info' => ['name' => 'John Doe', 'email' => 'john@example.com'],
        ]);
        $booking1->update(["status" => 'confirmed']); // Set status using new method

        $booking2 = Booking::create([ // Assigned to booking2
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            // 'status' => 'confirmed', // Removed
            'total_price' => 100,
            'guest_info' => ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ]);
        $booking2->update(["status" => 'confirmed']); // Set status using new method

        $this->actingAs($user);

        // Search for John
        $response = $this->get(route('admin.bookings.index', ['search' => 'John']));
        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->has('bookings', 1)
                ->where('bookings.0.guest_info.name', 'John Doe')
        );

        // Search for Jane's email
        $response = $this->get(route('admin.bookings.index', ['search' => 'jane@example.com']));
        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->has('bookings', 1)
                ->where('bookings.0.guest_info.name', 'Jane Smith')
        );
    }

    public function test_admin_can_export_bookings_csv()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $booking = Booking::create([ // Assigned to booking
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            // 'status' => 'confirmed', // Removed
            'total_price' => 100,
            'guest_info' => ['name' => 'Export Guest', 'email' => 'export@example.com'],
        ]);
        $booking->update(["status" => 'confirmed']); // Set status using new method

        $this->actingAs($user);

        $response = $this->get(route('admin.bookings.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=bookings.csv');

        $content = $response->streamedContent();
        $this->assertStringContainsString('ID,Property,"Guest Name",Email,Phone,Check-in,Check-out,Status,"Total Price"', $content);
        $this->assertStringContainsString('Export Guest', $content);
        $this->assertStringContainsString('export@example.com', $content);
    }
}
