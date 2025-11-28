<?php

namespace App\Data\Admin;

use App\Data\Shared\CustomerData;
use App\Data\Shared\GuestData;
use App\Data\Shared\PaymentData;
use App\Data\Shared\PropertyData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class BookingDetailData extends Data
{
    public function __construct(
        public int $id,
        public ?string $code,
        public PropertyData $property,
        public ?CustomerData $customer,
        public string $check_in_date,
        public string $check_out_date,
        public int $total_price_amount,
        public string $status,
        public ?string $notes,
        public string $created_at,
        public string $updated_at,
        /** @var DataCollection<PaymentData> */
        public DataCollection $payments,
        public ?string $checkin_token,
        /** @var DataCollection<GuestData> */
        public DataCollection $guests,
    ) {}

    public static function fromModel($booking): self
    {
        return new self(
            id: $booking->id,
            code: $booking->code,
            property: PropertyData::from($booking->property),
            customer: $booking->customer ? CustomerData::from($booking->customer) : null,
            check_in_date: $booking->check_in_date->format('Y-m-d'),
            check_out_date: $booking->check_out_date->format('Y-m-d'),
            total_price_amount: $booking->total_price_amount,
            status: $booking->status,
            notes: $booking->notes,
            created_at: $booking->created_at->toISOString(),
            updated_at: $booking->updated_at->toISOString(),
            payments: PaymentData::collect($booking->payments, DataCollection::class),
            checkin_token: $booking->checkin_token,
            guests: GuestData::collect($booking->guests, DataCollection::class),
        );
    }
}
