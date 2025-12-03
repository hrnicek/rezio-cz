<?php

namespace App\Exceptions\Booking;

class ServiceCalculationException extends BookingException
{
    public function __construct(string $message = 'Error calculating service prices.', array $context = [])
    {
        parent::__construct($message, $context, 500);
    }
}