<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_booking_dates_and_status()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'status' => 'pending',
            'total_price' => 100,
            'guest_info' => ['name' => 'Test Guest'],
        ]);

        $this->actingAs($user);

        $newStart = now()->addDays(2)->startOfDay();
        $newEnd = now()->addDays(4)->startOfDay();

        $response = $this->put(route('bookings.update', $booking), [
            'status' => 'confirmed',
            'start_date' => $newStart->format('Y-m-d'),
            'end_date' => $newEnd->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals($newStart->format('Y-m-d'), $booking->start_date->format('Y-m-d'));
        $this->assertEquals($newEnd->format('Y-m-d'), $booking->end_date->format('Y-m-d'));
    }

    public function test_cannot_update_booking_dates_to_overlap()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $booking1 = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'status' => 'confirmed',
            'total_price' => 100,
            'guest_info' => ['name' => 'Guest 1'],
        ]);

        $booking2 = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'status' => 'confirmed',
            'total_price' => 100,
            'guest_info' => ['name' => 'Guest 2'],
        ]);

        $this->actingAs($user);

        // Try to move booking2 to overlap with booking1
        $response = $this->put(route('bookings.update', $booking2), [
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
        $property = Property::factory()->create(['user_id' => $user->id]);
        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'status' => 'pending',
            'total_price' => 100,
            'guest_info' => ['name' => 'Test Guest'],
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('bookings.destroy', $booking));

        $response->assertRedirect();
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }
}
