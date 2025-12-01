<?php

namespace App\States\Booking;

class CheckedIn extends BookingState
{
    public static string $name = 'checked_in';

    public function label(): string
    {
        return 'Ubytován';
    }

    public function color(): string
    {
        return 'blue';
    }
}
