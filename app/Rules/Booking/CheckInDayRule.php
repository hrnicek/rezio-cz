<?php

namespace App\Rules\Booking;

use Carbon\Carbon;
use App\Models\Season;

class CheckInDayRule implements BookingRule
{
    public function validate(Carbon $start, Carbon $end, ?Season $season): void
    {
        if (!$season || empty($season->check_in_days)) {
            return;
        }

        // check_in_days should be an array of day names or numbers (e.g. ['Friday', 'Saturday'] or [5, 6])
        // Assuming season model casts check_in_days to array

        $dayName = $start->englishDayOfWeek; // e.g. 'Friday'

        if (!in_array($dayName, $season->check_in_days)) {
            $allowed = implode(', ', $season->check_in_days);
            throw new \Exception("Check-in is only allowed on: {$allowed}.");
        }
    }
}
