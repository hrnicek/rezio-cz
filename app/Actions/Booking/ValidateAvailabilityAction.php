<?php

namespace App\Actions\Booking;

use App\Models\Booking\Booking;
use App\Models\Configuration\BlockDate;
use Carbon\Carbon;

class ValidateAvailabilityAction
{
    public function execute(Carbon $startDate, Carbon $endDate): array
    {
        // Check for overlapping bookings using datetime boundaries
        $overlapping = Booking::where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('date_start', '<', $endDate)
                        ->where('date_end', '>', $startDate);
                });
            })
            ->exists();

        // Check for blackout dates
        $blackouts = BlockDate::where(function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<', $endDate->toDateString())
                    ->where('end_date', '>', $startDate->toDateString());
            });
        })->exists();

        $isAvailable = ! $overlapping && ! $blackouts;

        return [
            'available' => $isAvailable,
            'has_overlapping_bookings' => $overlapping,
            'has_blackout_dates' => $blackouts,
        ];
    }
}
