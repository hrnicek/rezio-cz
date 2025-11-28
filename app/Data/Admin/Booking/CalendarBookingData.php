<?php

namespace App\Data\Admin\Booking;

use App\Enums\BookingStatus;
use Spatie\LaravelData\Data;

class CalendarBookingData extends Data
{
    public function __construct(
        public string $id,
        public string $key,
        public string $start,
        public string $end,
        public string $title,
        public BookingStatus $status,
    ) {}

    public static function fromModel($booking): self
    {
        return new self(
            id: $booking->id,
            key: $booking->id,
            start: $booking->check_in_date->format('Y-m-d'),
            end: $booking->check_out_date->format('Y-m-d'),
            title: $booking->customer
                ? $booking->customer->name
                : 'Unknown',
            status: $booking->status instanceof BookingStatus ? $booking->status : BookingStatus::from($booking->status),
        );
    }
}
