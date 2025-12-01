<?php

namespace App\States\Booking;

class NoShow extends BookingState
{
    public static string $name = 'no_show';

    public function label(): string
    {
        return 'Nedostavil se';
    }

    public function color(): string
    {
        return 'orange';
    }
}
