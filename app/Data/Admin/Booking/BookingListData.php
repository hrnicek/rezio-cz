<?php

namespace App\Data\Admin\Booking;

use App\Data\Shared\MoneyData;
use App\Models\Booking\Booking;
use Spatie\LaravelData\Data;

class BookingListData extends Data
{
    public function __construct(
        public string $id,
        public ?string $code,
        public string $status,
        public string $status_label,
        public string $customer_name,
        public ?string $property_name,

        public string $check_in_date,
        public string $check_out_date,

        public MoneyData $total_price,

        public string $created_at_human,
    ) {}

    public static function fromModel(Booking $booking): self
    {
        return new self(
            id: $booking->id,
            code: $booking->code,
            status: (string) $booking->status,
            status_label: $booking->status->label(),
            customer_name: $booking->customer?->billing_name ?? 'Neznámý',
            property_name: $booking->property?->name,
            check_in_date: $booking->check_in_date->format('d.m.Y'),
            check_out_date: $booking->check_out_date->format('d.m.Y'),
            total_price: MoneyData::fromModel($booking->total_price_amount, $booking->currency),
            created_at_human: $booking->created_at->diffForHumans(),
        );
    }
}
