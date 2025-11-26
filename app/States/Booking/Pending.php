<?php

namespace App\States\Booking;

class Pending extends BookingState
{
    public static $name = 'pending';

    public function color(): string
    {
        return 'gray';
    }

    public function label(): string
    {
        return 'Čekající';
    }
}
