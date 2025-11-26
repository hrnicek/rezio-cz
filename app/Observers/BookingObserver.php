<?php

namespace App\Observers;

use App\Models\Booking;
use Illuminate\Support\Str;

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
        if ($booking->total_price > 0 && $booking->status !== 'blocked') {
            $booking->payments()->create([
                'amount' => $booking->total_price,
                'payment_method' => 'transfer',
                'status' => 'pending',
                'paid_at' => null,
            ]);
        }

        $booking->checkin_token = Str::random(12);
        $booking->saveQuietly();
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = now()->format('y') . strtoupper(Str::random(4));
        } while (Booking::where('code', $code)->exists());

        return $code;
    }
}
