<?php

namespace App\Exceptions\Booking;

class BookingValidationException extends BookingException
{
    public function __construct(string $message, array $context = [])
    {
        parent::__construct($message, $context, 422);
    }
}