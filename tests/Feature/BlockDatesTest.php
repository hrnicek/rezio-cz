<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class BlockDatesTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_admin_can_block_dates()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $start = now()->addDays(1)->startOfDay();
        $end = now()->addDays(3)->startOfDay();

        $response = $this->from(route('admin.bookings.index'))->post(route('admin.bookings.store'), [ // Added ->from()
            'property_id' => $property->id,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'status' => 'blocked', // Added status to request payload
        ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('bookings', 1);
        $booking = Booking::first();
        $this->assertEquals('blocked', $booking->status); // Changed assertion
        $this->assertEquals($start->format('Y-m-d'), $booking->start_date->format('Y-m-d'));
        $this->assertEquals($end->format('Y-m-d'), $booking->end_date->format('Y-m-d'));
    }

    public function test_cannot_block_dates_with_overlap()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $existingBooking = Booking::create([ // Assigned to existingBooking
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'guest_info' => ['name' => 'Existing Guest'],
        ]);
        $existingBooking->update(["status" => 'confirmed']); // Set status using new method

        $this->actingAs($user);

        $response = $this->from(route('admin.bookings.index'))->post(route('admin.bookings.store'), [ // Added ->from()
            'property_id' => $property->id,
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(4)->format('Y-m-d'),
            'status' => 'blocked', // Added status to request payload
        ]);

        $response->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_blocked_dates_prevent_public_booking()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $blockedBooking = Booking::create([ // Assigned to blockedBooking
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 0,
            'guest_info' => ['name' => 'Blocked'],
        ]);
        $blockedBooking->update(["status" => 'blocked']); // Set status using new method

        $response = $this->from("/book/{$property->widget_token}")->post(route('client.widget.store', $property->widget_token), [ // Added ->from()
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'guest_name' => 'New Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '1234567890',
        ]);

        $response->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('bookings', 1);
    }
}
