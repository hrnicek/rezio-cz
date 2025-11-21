<?php

namespace App\Rules\Booking;

use Carbon\Carbon;
use App\Models\Season;

class MinStayRule implements BookingRule
{
    public function validate(Carbon $start, Carbon $end, ?Season $season): void
    {
        $nights = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());
        $minStay = $season?->min_stay ?? (int) config('booking.min_stay_default', 1);

        if ($nights < $minStay) {
            throw new \Exception("Minimum stay for this period is {$minStay} nights.");
        }
    }
}
