<?php

namespace App\States\Booking;

class Confirmed extends BookingState
{
    public static $name = 'confirmed';

    public function color(): string
    {
        return 'success';
    }

    public function label(): string
    {
        return 'Potvrzeno';
    }
}
