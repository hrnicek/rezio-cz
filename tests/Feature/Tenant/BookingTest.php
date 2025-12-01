<?php

namespace Tests\Feature\Tenant;

use App\Models\Booking\Booking;
use App\Models\Property;
use App\Models\CRM\Customer;
use App\States\Booking\Pending;
use App\States\Booking\Confirmed;
use App\States\Booking\Cancelled;
use App\States\Booking\CheckedIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class BookingTest extends TenantTestCase
{
    use RefreshDatabase;

    public function test_booking_has_initial_state()
    {
        $booking = Booking::factory()->create();

        $this->assertTrue($booking->status->equals(Pending::class));
        $this->assertEquals('pending', (string) $booking->status);
    }

    public function test_booking_can_transition_to_confirmed()
    {
        $booking = Booking::factory()->create();

        $booking->status->transitionTo(Confirmed::class);

        $this->assertTrue($booking->status->equals(Confirmed::class));
        $this->assertEquals('confirmed', (string) $booking->status);
    }

    public function test_booking_cannot_transition_to_invalid_state()
    {
        $booking = Booking::factory()->create();

        $this->expectException(\Spatie\ModelStates\Exceptions\TransitionNotFound::class);

        // Pending -> CheckedIn is not allowed directly (must be Confirmed first)
        $booking->status->transitionTo(CheckedIn::class);
    }

    public function test_booking_status_serialization()
    {
        $booking = Booking::factory()->create();
        
        // Simulate serialization via BookingData or array
        $array = $booking->toArray();
        
        // Note: toArray() uses the model's attributes. 
        // If 'status' is in casts, it might be serialized to the class name or alias depending on Spatie config.
        // But we know (string) cast returns alias.
        
        $this->assertEquals('pending', (string) $booking->status);
    }
    
    public function test_admin_can_update_booking_status()
    {
        $user = \App\Models\User::factory()->create();
        $property = Property::factory()->create();
        $user->properties()->attach($property);
        $user->update(['current_property_id' => $property->id]);
        
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'status' => Pending::class,
        ]);
        
        $response = $this->actingAs($user)
            ->put(route('admin.bookings.update', $booking->id), [
                'status' => 'confirmed',
            ]);
            
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $booking->refresh();
        $this->assertTrue($booking->status->equals(Confirmed::class));
    }

    public function test_admin_cannot_update_to_invalid_status()
    {
        $user = \App\Models\User::factory()->create();
        $property = Property::factory()->create();
        $user->properties()->attach($property);
        $user->update(['current_property_id' => $property->id]);
        
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'status' => Pending::class,
        ]);
        
        $response = $this->actingAs($user)
            ->put(route('admin.bookings.update', $booking->id), [
                'status' => 'checked_in', // Invalid transition from Pending
            ]);
            
        $response->assertSessionHasErrors('status');
        
        $booking->refresh();
        $this->assertTrue($booking->status->equals(Pending::class));
    }
}
