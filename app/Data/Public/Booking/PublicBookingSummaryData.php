<?php

namespace App\Data\Public\Booking;

use App\Data\Shared\MoneyData;
use Spatie\LaravelData\Data;

class PublicBookingSummaryData extends Data
{
    public function __construct(
        public string $code,
        public string $status_label,
        public string $property_name,
        public string $check_in_date,
        public string $check_out_date,
        public MoneyData $total_price,
    ) {}
}
