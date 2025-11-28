<?php

namespace App\Data\Shared;

use Spatie\LaravelData\Data;

class AddressData extends Data
{
    public function __construct(
        public ?string $street,
        public ?string $city,
        public ?string $zip,
        public ?string $country,
    ) {}
}
