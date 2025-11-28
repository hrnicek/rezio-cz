<?php

namespace App\Data\Admin\Booking;

use Spatie\LaravelData\Data;

class CreateBookingData extends Data
{
    public function __construct(
        public int $property_id,
        public string $start_date,
        public string $end_date,
        public ?string $customer_id,
        // Add other fields as necessary
    ) {}
}
