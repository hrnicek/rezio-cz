<?php

namespace App\Data\Admin;

use Spatie\LaravelData\Data;

class CalendarBookingData extends Data
{
    public function __construct(
        public int $id,
        public int $key,
        public string $start,
        public string $end,
        public string $title,
        public string $status,
    ) {}

    public static function fromModel($booking): self
    {
        return new self(
            id: $booking->id,
            key: $booking->id,
            start: $booking->start_date->format('Y-m-d'),
            end: $booking->end_date->format('Y-m-d'),
            title: $booking->customer
                ? $booking->customer->first_name . ' ' . $booking->customer->last_name
                : 'Unknown',
            status: $booking->status ?? '',
        );
    }
}
