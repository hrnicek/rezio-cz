<?php

namespace App\Data\Admin\Booking;

use App\Data\Shared\MoneyData;
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
        public ?MoneyData $total_price = null,
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
            total_price: MoneyData::fromModel($booking->total_price_amount, $booking->currency),
            guests: ($booking->adults ?? 0) + ($booking->children ?? 0),
        );
    }
}
