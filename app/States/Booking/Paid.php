<?php

namespace App\States\Booking;

class Paid extends BookingState
{
    public static $name = 'paid';

    public function color(): string
    {
        return 'success';
    }
}
