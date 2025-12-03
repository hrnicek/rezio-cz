<?php

namespace App\Actions\Booking;

use App\Models\Booking\Booking;
use App\Services\BookingService;

class CreateBookingAction
{
    public function __construct(private BookingService $bookingService) {}

    /**
     * Create a new booking using the improved BookingService
     *
     * @param array $data Booking data including property_id, dates, customer info, addons, etc.
     * @return Booking The created booking with relationships loaded
     *
     * @throws \App\Exceptions\Booking\BookingException
     */
    public function execute(array $data): Booking
    {
        return $this->bookingService->createBooking($data);
    }
}
