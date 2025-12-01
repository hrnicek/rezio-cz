<?php

namespace App\States\Booking;

class Pending extends BookingState
{
    public static string $name = 'pending';

    public function label(): string
    {
        return 'Čekající';
    }

    public function color(): string
    {
        return 'yellow';
    }
}
