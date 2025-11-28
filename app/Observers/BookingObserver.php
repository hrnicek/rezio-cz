<?php

namespace App\Observers;

use Illuminate\Support\Str;
use App\Models\Booking\Booking;

class BookingObserver
{
    public function creating(Booking $booking): void
    {
        if (empty($booking->code)) {
            $booking->code = $this->generateUniqueCode();
        }
    }

    public function created(Booking $booking): void
    {
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = now()->format('y').strtoupper(Str::random(4));
        } while (Booking::where('code', $code)->exists());

        return $code;
    }
}
