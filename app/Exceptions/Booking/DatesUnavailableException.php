<?php

namespace App\Exceptions\Booking;

class DatesUnavailableException extends BookingException
{
    public function __construct(array $context = [])
    {
        parent::__construct('Selected dates are not available for booking.', $context, 422);
    }
}