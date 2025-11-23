<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenantTestCase;

class BookingTest extends TenantTestCase
{
    public function test_bookings_index_is_displayed(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        // $user->update(['current_property_id' => $property->id]); // Removed as current_property_id is removed

        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('admin.bookings.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Admin/Properties/Bookings/Index')
                ->has('bookings.data', 1)
        );
    }

    public function test_bookings_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        // $user->update(['current_property_id' => $property->id]);

        $bookingPending = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $bookingConfirmed = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'total_price' => 200,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->get(route('admin.bookings.index', ['status' => 'confirmed']));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Admin/Properties/Bookings/Index')
                ->has('bookings.data', 1)
                ->where('bookings.data.0.id', $bookingConfirmed->id)
                ->where('bookings.data.0.status', 'confirmed')
        );

        $confirmedBooking = Booking::find($bookingConfirmed->id);
        $this->assertEquals('confirmed', $confirmedBooking->status);
    }

    public function test_booking_status_can_be_updated(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $property = Property::factory()->create();
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->from(route('admin.bookings.index'))->put(route('admin.bookings.update', $booking), [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);
    }

    public function test_booking_can_be_deleted(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $property = Property::factory()->create();
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->from(route('admin.bookings.index'))->delete(route('admin.bookings.destroy', $booking));

        $response->assertRedirect();
        $this->assertSoftDeleted('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_user_can_update_other_users_bookings_temporarily(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $property = Property::factory()->create();
        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user1->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user2)
            ->from(route('admin.bookings.index'))
            ->put(route('admin.bookings.update', $booking->id), [
                'status' => 'confirmed',
            ]);

        $response->assertRedirect();
        $this->assertEquals('confirmed', $booking->refresh()->status);
    }
    public function test_booking_can_be_created_without_user_id(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();

        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => null,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'user_id' => null,
        ]);
    }
}
