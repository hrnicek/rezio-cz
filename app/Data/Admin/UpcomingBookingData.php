<?php

namespace App\Data\Admin;

use Spatie\LaravelData\Data;

class UpcomingBookingData extends Data
{
    public function __construct(
        public int $id,
        public string $label,
        public string $start,
        public string $end,
    ) {}

    public static function fromModel($booking): self
    {
        $customerName = $booking->customer
            ? $booking->customer->first_name.' '.$booking->customer->last_name
            : 'Unknown';

        return new self(
            id: $booking->id,
            label: $customerName.' ('.($booking->status ?? '').')',
            start: $booking->start_date->format('Y-m-d'),
            end: $booking->end_date->format('Y-m-d'),
        );
    }
}
