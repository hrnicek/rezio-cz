<?php

namespace App\Data\Admin\Booking;

use App\Data\Shared\MoneyData;
use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class BookingListData extends Data
{
    public function __construct(
        public string $id,
        public ?string $code,
        public string $status,
        public string $status_label,
        public string $customer_name,

        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y')]
        public Carbon $check_in_date,

        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y')]
        public Carbon $check_out_date,

        public MoneyData $total_price,

        public string $created_at_human,
    ) {}

    public static function fromModel(Booking $booking): self
    {
        return new self(
            id: $booking->id,
            code: $booking->code,
            status: $booking->status instanceof BookingStatus ? $booking->status->value : $booking->status,
            status_label: $booking->status instanceof BookingStatus ? $booking->status->label() : $booking->status,
            customer_name: $booking->customer?->billing_name ?? 'Neznámý',
            check_in_date: $booking->check_in_date,
            check_out_date: $booking->check_out_date,
            total_price: MoneyData::fromModel($booking->total_price_amount, $booking->currency),
            created_at_human: $booking->created_at->diffForHumans(),
        );
    }
}
