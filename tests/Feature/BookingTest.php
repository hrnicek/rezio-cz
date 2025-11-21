<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookings_index_is_displayed(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'guest_info' => ['name' => 'Test Guest', 'email' => 'test@example.com', 'phone' => '123'],
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/bookings');

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Bookings/Index')
                ->has('bookings', 1)
        );
    }

    public function test_bookings_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'guest_info' => ['name' => 'Pending Guest'],
            'total_price' => 200,
            'status' => 'pending',
        ]);

        Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'guest_info' => ['name' => 'Confirmed Guest'],
            'total_price' => 200,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->get('/bookings?status=confirmed');

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Bookings/Index')
                ->has('bookings', 1)
                ->where('bookings.0.status', 'confirmed')
        );
    }

    public function test_booking_status_can_be_updated(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'guest_info' => ['name' => 'Test Guest'],
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->put("/bookings/{$booking->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_booking_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'guest_info' => ['name' => 'Test Guest'],
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->delete("/bookings/{$booking->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_user_cannot_manage_others_bookings(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user1->id]);
        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $user1->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'guest_info' => ['name' => 'Test Guest'],
            'total_price' => 200,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user2)->put("/bookings/{$booking->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(403);
    }
}
