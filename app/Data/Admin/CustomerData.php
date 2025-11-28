<?php

namespace App\Data\Admin;

use App\Models\Customer;
use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public string $id, // UUID
        public string $name, // Kontaktní osoba
        public string $email,
        public ?string $phone,
        
        // Company Info
        public bool $is_company,
        public ?string $company_name,
        public ?string $ico,
        public ?string $dic,
        public bool $has_vat,
        
        // Billing Address
        public ?string $billing_street,
        public ?string $billing_city,
        public ?string $billing_zip,
        public string $billing_country,
        
        public ?string $internal_notes,
        public bool $is_registered,
        
        public ?string $created_at,
    ) {}

    public static function fromModel(Customer $customer): self
    {
        return new self(
            id: $customer->id,
            name: $customer->name,
            email: $customer->email,
            phone: $customer->phone,
            
            is_company: $customer->is_company,
            company_name: $customer->company_name,
            ico: $customer->ico,
            dic: $customer->dic,
            has_vat: $customer->has_vat,
            
            billing_street: $customer->billing_street,
            billing_city: $customer->billing_city,
            billing_zip: $customer->billing_zip,
            billing_country: $customer->billing_country ?? 'CZ',
            
            internal_notes: $customer->internal_notes,
            is_registered: $customer->is_registered,
            
            created_at: $customer->created_at?->toIso8601String(),
        );
    }
}
