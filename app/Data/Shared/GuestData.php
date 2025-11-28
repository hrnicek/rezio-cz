<?php

namespace App\Data\Shared;

use Spatie\LaravelData\Data;

class GuestData extends Data
{
    public function __construct(
        public int $id,
        public string $first_name,
        public string $last_name,
        public bool $is_adult,
        public ?string $gender,
        public ?string $nationality,
        public ?string $document_type,
        public ?string $document_number,
        public ?string $birth_date,
        public ?string $birth_place,
        public ?array $address,
        public ?string $signature,
    ) {}
}
