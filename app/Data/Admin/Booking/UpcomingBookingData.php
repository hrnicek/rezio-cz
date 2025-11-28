<?php

namespace App\Data\Admin\Booking;

use App\Enums\BookingStatus;
use Spatie\LaravelData\Data;

class UpcomingBookingData extends Data
{
    public function __construct(
        public string $id,
        public string $label,
        public string $start,
        public string $end,
    ) {}

    public static function fromModel($booking): self
    {
        $customerName = $booking->customer
            ? $booking->customer->name
            : 'Unknown';

        $statusLabel = $booking->status instanceof BookingStatus 
            ? $booking->status->label() 
            : $booking->status;

        return new self(
            id: $booking->id,
            label: $customerName.' ('.$statusLabel.')',
            start: $booking->check_in_date->format('Y-m-d'),
            end: $booking->check_out_date->format('Y-m-d'),
        );
    }
}
