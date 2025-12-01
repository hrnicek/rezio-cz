<?php

namespace App\Data\Admin\Booking;

use Spatie\LaravelData\Data;

class CalendarBookingData extends Data
{
    public function __construct(
        public string $id,
        public string $key,
        public string $start,
        public string $end,
        public string $title,
        public string $status,
        public ?string $total_price = null,
        public ?int $guests = null,
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
            status: (string) $booking->status,
            total_price: $booking->total_price_amount ? number_format($booking->total_price_amount, 0, ',', ' ') . ' ' . $booking->total_price_currency : null,
            guests: ($booking->adults ?? 0) + ($booking->children ?? 0),
        );
    }
}
