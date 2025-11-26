<?php

namespace App\States\Booking;

class Cancelled extends BookingState
{
    public static $name = 'cancelled';

    public function color(): string
    {
        return 'danger';
    }

    public function label(): string
    {
        return 'Zrušeno';
    }
}
