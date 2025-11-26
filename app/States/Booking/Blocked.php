<?php

namespace App\States\Booking;

class Blocked extends BookingState
{
    public static $name = 'blocked';

    public function color(): string
    {
        return 'gray';
    }

    public function label(): string
    {
        return 'Blokováno';
    }
}
