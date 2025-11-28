<?php

namespace App\Data\Shared;

use Spatie\LaravelData\Data;

class PropertyData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $address,
        public ?string $image = null,
        public ?string $description = null,
    ) {}
}
