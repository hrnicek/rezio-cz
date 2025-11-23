<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class CheckInTest extends TenantTestCase
{

    public function test_checkin_page_can_be_rendered()
    {
        $property = Property::factory()->create();
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'checkin_token' => 'test-token',
        ]);

        $response = $this->get(route('check-in.show', $booking->checkin_token));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Guest/CheckIn/Show')
                ->has('booking')
                ->has('guests')
        );
    }

    public function test_guest_can_be_stored()
    {
        $property = Property::factory()->create();
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'checkin_token' => 'test-token',
        ]);

        $response = $this->post(route('check-in.guests.store', $booking->checkin_token), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_adult' => true,
            'gender' => 'male',
            'nationality' => 'US',
            'document_type' => 'passport',
            'document_number' => '123456789',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => '123 Main St',
                'city' => 'New York',
                'zip' => '10001',
                'country' => 'USA',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('guests', [
            'booking_id' => $booking->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'document_number' => '123456789',
        ]);
    }

    public function test_guest_can_be_updated()
    {
        $property = Property::factory()->create();
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'checkin_token' => 'test-token',
        ]);
        $guest = Guest::create([
            'booking_id' => $booking->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_adult' => true,
        ]);

        $response = $this->put(route('check-in.guests.update', [$booking->checkin_token, $guest->id]), [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'is_adult' => true,
            'gender' => 'female',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'gender' => 'female',
        ]);
    }

    public function test_guest_can_be_deleted()
    {
        $property = Property::factory()->create();
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'checkin_token' => 'test-token',
        ]);
        $guest = Guest::create([
            'booking_id' => $booking->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'is_adult' => true,
        ]);

        $response = $this->delete(route('check-in.guests.destroy', [$booking->checkin_token, $guest->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('guests', [
            'id' => $guest->id,
        ]);
    }

    public function test_cannot_access_checkin_with_invalid_token()
    {
        $response = $this->get(route('check-in.show', 'invalid-token'));

        $response->assertStatus(404);
    }
}
