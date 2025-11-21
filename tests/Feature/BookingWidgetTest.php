<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_widget_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->get("/book/{$property->widget_token}");

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Widget/Show')
                ->has('property')
        );
    }

    public function test_guest_can_submit_booking(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $user = User::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user->id,
            'price_per_night' => 100,
        ]);

        $response = $this->post("/book/{$property->widget_token}", [
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '123456789',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bookings', [
            'property_id' => $property->id,
            'user_id' => $user->id,
            'total_price' => 200, // 2 nights * 100
            'status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\BookingConfirmation::class, function ($mail) use ($response) {
            return $mail->hasTo('john@example.com');
        });

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\NewBookingAlert::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_cannot_book_overlapping_dates(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        // Create existing booking
        Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'guest_info' => ['name' => 'Existing Guest'],
            'total_price' => 500,
            'status' => 'confirmed',
        ]);

        // Try to book overlapping dates
        $response = $this->post("/book/{$property->widget_token}", [
            'start_date' => now()->addDays(8)->format('Y-m-d'),
            'end_date' => now()->addDays(12)->format('Y-m-d'),
            'guest_name' => 'New Guest',
            'guest_email' => 'new@example.com',
            'guest_phone' => '987654321',
        ]);

        $response->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('bookings', 1);
    }
}
