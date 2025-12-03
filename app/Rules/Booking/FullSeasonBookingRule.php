<?php

namespace App\Rules\Booking;

use App\Models\Configuration\Season;
use Carbon\Carbon;

class FullSeasonBookingRule implements BookingRule
{
    public function validate(Carbon $start, Carbon $end, ?Season $season, int $guestsCount = 1): void
    {
        if (! $season || ! $season->is_full_season_booking_only) {
            return;
        }

        if (! $season->start_date || ! $season->end_date) {
            return;
        }

        // Determine the expected start and end dates for the season in the year of the booking
        // If is_recurring is true, we need to map season dates to the booking year
        
        // However, the season logic is usually:
        // If season matches date, we found the season.
        // If is_full_season_booking_only is true, the booking start/end must match season start/end.
        
        // Since season matching logic handles recurrence, the $season object passed here is the matched season.
        // But $season->start_date has the YEAR of definition (which might be 2024, while booking is 2025).
        
        // So we need to check if the booking covers the WHOLE period defined by the season,
        // accounting for year differences if recurring.
        
        // Simple check: duration must match season duration AND start date must align with season start (month/day).
        
        $seasonStart = $season->start_date;
        $seasonEnd = $season->end_date;
        
        $seasonDays = $seasonStart->diffInDays($seasonEnd);
        // Booking days (nights?) - usually start to end is nights? 
        // But here start and end are Check-in and Check-out.
        // So duration is diffInDays.
        
        $bookingDays = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());
        
        // If durations differ, it's not full season.
        if ($bookingDays !== $seasonDays) {
             throw new \Exception("This season can only be booked as a whole ({$seasonDays} nights).");
        }
        
        // Also check if start date matches season start date (month/day)
        if ($start->format('md') !== $seasonStart->format('md')) {
             throw new \Exception("Booking must start on the first day of the season.");
        }
    }
}
