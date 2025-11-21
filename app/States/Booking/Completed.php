<?php

namespace App\States\Booking;

class Completed extends BookingState
{
    public static $name = 'completed';

    public function color(): string
    {
        return 'info';
    }
}
