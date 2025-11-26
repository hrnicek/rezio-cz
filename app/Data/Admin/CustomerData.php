<?php

namespace App\Data\Admin;

use App\Models\Customer;
use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public ?string $first_name,
        public ?string $last_name,
        public ?string $name, // Calculated field
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public ?string $zip,
        public ?string $country,
        public ?string $company_name,
        public ?string $vat_id,
        public ?string $notes,
        public string $status,
        public ?string $created_at,
    ) {}

    public static function fromModel(Customer $customer): self
    {
        return new self(
            id: $customer->id,
            uuid: $customer->uuid,
            first_name: $customer->first_name,
            last_name: $customer->last_name,
            name: $customer->name,
            email: $customer->email,
            phone: $customer->phone,
            address: $customer->address,
            city: $customer->city,
            zip: $customer->zip,
            country: $customer->country,
            company_name: $customer->company_name,
            vat_id: $customer->vat_id,
            notes: $customer->notes,
            status: $customer->status ?? 'active',
            created_at: $customer->created_at?->toIso8601String(),
        );
    }
}
