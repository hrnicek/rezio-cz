<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class BookingSearchExportTest extends TenantTestCase
{

    public function test_admin_can_search_bookings(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $user->update(['current_property_id' => $property->id]);

        $john = \App\Models\Customer::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
        ]);

        $jane = \App\Models\Customer::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '987654321',
        ]);

        Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'customer_id' => $john->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'status' => 'pending',
        ]);
        Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'customer_id' => $jane->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'total_price' => 300,
            'status' => 'confirmed',
        ]);

        // Search for John's name
        $response = $this->actingAs($user)->get(route('admin.bookings.index', ['search' => 'John']));
        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->has('bookings.data', 1)
        );

        // Search for Jane's email
        $response = $this->actingAs($user)->get(route('admin.bookings.index', ['search' => 'jane@example']));
        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->has('bookings.data', 1)
        );
    }

    public function test_admin_can_export_bookings_csv()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();

        $exportCustomer = \App\Models\Customer::create([
            'first_name' => 'Export',
            'last_name' => 'Guest',
            'email' => 'export@example.com',
            'phone' => '000000000',
        ]);

        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'customer_id' => $exportCustomer->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            // 'status' => 'confirmed', // Removed
            'total_price' => 100,
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
