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
        $overlapping = Booking::query()->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('check_in_date', '<', $endDate)
                        ->where('check_out_date', '>', $startDate);
                });
            })
            ->exists();

        // Check for blackout dates
        $blackouts = BlockDate::query()->where(function ($query) use ($startDate, $endDate) {
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
