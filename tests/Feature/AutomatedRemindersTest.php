<?php

namespace Tests\Feature;

use App\Mail\BookingReminder;
use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TenantTestCase;

class AutomatedRemindersTest extends TenantTestCase
{

    public function test_sends_reminders_for_bookings_starting_in_3_days()
    {
        Mail::fake();

        $user = User::factory()->create();
        $property = Property::factory()->create();

        // Booking starting in 3 days
        $bookingToRemind = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id, // Explicitly set user_id
            'start_date' => now()->addDays(3),
            'end_date' => now()->addDays(5),
            'reminders_sent_at' => null,
        ]);
        $bookingToRemind->update(["status" => 'confirmed']); // Set status using new method

        // Booking starting in 4 days (should not remind)
        $bookingTooEarly = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id, // Explicitly set user_id
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'reminders_sent_at' => null,
        ]);
        $bookingTooEarly->update(["status" => 'confirmed']); // Set status using new method

        // Booking already reminded (should not remind)
        $bookingAlreadyReminded = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id, // Explicitly set user_id
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'reminders_sent_at' => now(),
        ]);
        $bookingAlreadyReminded->update(["status" => 'confirmed']); // Set status using new method

        // Run the command
        $this->artisan('bookings:send-reminders')
            ->expectsOutput('Found 1 bookings to remind.')
            ->assertExitCode(0);

        // Assert email sent
        Mail::assertSent(BookingReminder::class, 1);

        // Assert database updated
        $this->assertNotNull($bookingToRemind->fresh()->reminders_sent_at);
        $this->assertEquals('confirmed', $bookingToRemind->fresh()->status);
    }
}
