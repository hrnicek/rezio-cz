<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class BookingNotesTest extends TenantTestCase
{

    public function test_admin_can_add_notes_to_booking(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();

        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
        ]);
        $booking->update(["status" => 'confirmed']); // Set status using new method

        $this->actingAs($user);

        $response = $this->from(route('admin.bookings.index'))->put(route('admin.bookings.update', $booking), [ // Added ->from()
            'status' => 'confirmed',
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
            'notes' => 'Guest requested early check-in',
        ]);

        $response->assertRedirect();

        $booking->refresh();
        $this->assertEquals('Guest requested early check-in', $booking->notes);
        $this->assertEquals('confirmed', $booking->status); // Assert on status
    }

    public function test_notes_appear_in_csv_export(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();

        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'notes' => 'Special dietary requirements',
        ]);
        $booking->update(["status" => 'pending']); // Set a default status

        $this->actingAs($user);

        $response = $this->get(route('admin.bookings.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Notes', $content);
        $this->assertStringContainsString('Special dietary requirements', $content);
    }

    public function test_notes_are_optional(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();

        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'notes' => null,
        ]);
        $booking->update(["status" => 'confirmed']); // Set a default status

        $this->actingAs($user);

        $response = $this->from(route('admin.bookings.index'))->put(route('admin.bookings.update', $booking), [ // Added ->from()
            'status' => 'confirmed',
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertNull($booking->notes);
        $this->assertEquals('confirmed', $booking->status); // Assert on status
    }
}
