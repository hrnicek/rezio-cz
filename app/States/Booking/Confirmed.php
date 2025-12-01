<?php

namespace App\States\Booking;

class Confirmed extends BookingState
{
    public static string $name = 'confirmed';

    public function label(): string
    {
        return 'Potvrzeno';
    }

    public function color(): string
    {
        return 'green';
    }
}
