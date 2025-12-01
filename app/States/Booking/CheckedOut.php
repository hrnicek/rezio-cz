<?php

namespace App\States\Booking;

class CheckedOut extends BookingState
{
    public static string $name = 'checked_out';

    public function label(): string
    {
        return 'Odhlášen';
    }

    public function color(): string
    {
        return 'gray';
    }
}
