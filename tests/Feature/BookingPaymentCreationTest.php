<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPaymentCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_payment_when_booking_is_created()
    {
        $booking = Booking::factory()->create([
            'total_price' => 1000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('booking_payments', [
            'booking_id' => $booking->id,
            'amount' => 1000,
            'payment_method' => 'transfer',
            'status' => 'pending',
        ]);

        $this->assertEquals(1, $booking->payments()->count());
    }

    /** @test */
    public function it_does_not_create_payment_for_blocked_dates()
    {
        $booking = Booking::factory()->create([
            'total_price' => 0,
            'status' => 'blocked',
        ]);

        $this->assertEquals(0, $booking->payments()->count());
    }

    /** @test */
    public function it_does_not_create_payment_if_price_is_zero()
    {
        $booking = Booking::factory()->create([
            'total_price' => 0,
            'status' => 'confirmed',
        ]);

        $this->assertEquals(0, $booking->payments()->count());
    }
}
