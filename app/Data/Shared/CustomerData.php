<?php

namespace App\Data\Shared;

use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public string $id,
        public string $email,
        public string $name, // Kontaktní osoba (Composite)
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $phone = null,
        public bool $is_company = false,
        public ?string $company_name = null,
        public ?string $ico = null,
        public ?string $dic = null,
        public ?string $billing_street = null,
        public ?string $billing_city = null,
        public ?string $billing_zip = null,
        public ?string $billing_country = null,
        public ?string $internal_notes = null,
    ) {}
}
