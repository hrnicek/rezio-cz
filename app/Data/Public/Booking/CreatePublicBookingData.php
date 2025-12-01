<?php

namespace App\Data\Public\Booking;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class CreatePublicBookingData extends Data
{
    public function __construct(
        #[Rule('required|integer|exists:properties,id')]
        public int $property_id,

        #[Rule('required|date|after:today')]
        public string $check_in_date,

        #[Rule('required|date|after:check_in_date')]
        public string $check_out_date,

        #[Rule('required|integer|min:1')]
        public int $guests_count,

        // Customer info
        #[Rule('required|string|max:255')]
        public string $first_name,

        #[Rule('required|string|max:255')]
        public string $last_name,

        #[Rule('required|email')]
        public string $email,

        #[Rule('required|string|max:20')]
        public string $phone,
    ) {}
}
