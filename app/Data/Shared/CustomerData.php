<?php

namespace App\Data\Shared;

use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public int $id,
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone,
        public ?string $note,
    ) {}
}
