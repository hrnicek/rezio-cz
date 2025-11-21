<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_notes_to_booking(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('bookings.update', $booking), [
            'status' => 'confirmed',
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
            'notes' => 'Guest requested early check-in',
        ]);

        $response->assertRedirect();

        $booking->refresh();
        $this->assertEquals('Guest requested early check-in', $booking->notes);
    }

    public function test_notes_appear_in_csv_export(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'notes' => 'Special dietary requirements',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('bookings.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Notes', $content);
        $this->assertStringContainsString('Special dietary requirements', $content);
    }

    public function test_notes_are_optional(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'notes' => null,
        ]);

        $this->actingAs($user);

        $response = $this->put(route('bookings.update', $booking), [
            'status' => 'confirmed',
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertNull($booking->notes);
    }
}
