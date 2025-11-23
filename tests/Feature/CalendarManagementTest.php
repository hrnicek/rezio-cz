<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class CalendarManagementTest extends TenantTestCase
{

    public function test_admin_can_update_booking_dates_and_status()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $booking = Booking::create([ // Assigned to booking
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
        ]);
        $booking->update(["status" => 'pending']); // Set status using new method

        $this->actingAs($user);

        $newStart = now()->addDays(2)->startOfDay();
        $newEnd = now()->addDays(4)->startOfDay();

        $response = $this->from(route('admin.bookings.index'))->put(route('admin.bookings.update', $booking), [ // Added ->from()
            'status' => 'confirmed',
            'start_date' => $newStart->format('Y-m-d'),
            'end_date' => $newEnd->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status); // Changed assertion
        $this->assertEquals($newStart->format('Y-m-d'), $booking->start_date->format('Y-m-d'));
        $this->assertEquals($newEnd->format('Y-m-d'), $booking->end_date->format('Y-m-d'));
    }

    public function test_cannot_update_booking_dates_to_overlap()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();

        $booking1 = Booking::create([ // Assigned to booking1
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
        ]);
        $booking1->update(["status" => 'confirmed']); // Set status using new method

        $booking2 = Booking::create([ // Assigned to booking2
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'total_price' => 100,
        ]);
        $booking2->update(["status" => 'confirmed']); // Set status using new method

        $this->actingAs($user);

        // Try to move booking2 to overlap with booking1
        $response = $this->from(route('admin.bookings.index'))->put(route('admin.bookings.update', $booking2), [ // Added ->from()
            'status' => 'confirmed',
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(4)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('start_date');
        $booking2->refresh();
        $this->assertEquals(now()->addDays(5)->format('Y-m-d'), $booking2->start_date->format('Y-m-d'));
    }

    public function test_admin_can_delete_booking()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $booking = Booking::create([ // Assigned to booking
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
        ]);
        $booking->update(["status" => 'pending']); // Set status using new method

        $this->actingAs($user);

        $response = $this->from(route('admin.bookings.index'))->delete(route('admin.bookings.destroy', $booking)); // Added ->from()

        $response->assertRedirect();
        $this->assertSoftDeleted('bookings', ['id' => $booking->id]);
    }
}
