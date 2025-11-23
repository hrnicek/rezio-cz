<?php

namespace App\Data\Admin;

use App\Data\Shared\CustomerData;
use App\Data\Shared\PropertyData;
use App\Data\Shared\PaymentData;
use App\Data\Shared\GuestData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class BookingDetailData extends Data
{
    public function __construct(
        public int $id,
        public ?string $code,
        public PropertyData $property,
        public ?CustomerData $customer,
        public string $start_date,
        public string $end_date,
        public int $total_price,
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
            start_date: $booking->start_date->format('Y-m-d'),
            end_date: $booking->end_date->format('Y-m-d'),
            total_price: (int) $booking->total_price,
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
