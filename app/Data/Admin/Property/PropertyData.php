<?php

namespace App\Data\Admin\Property;

use Spatie\LaravelData\Data;

class PropertyData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $address,
        public ?string $image = null,
        public ?string $description = null,
        public string $default_check_in_time = '15:00:00',
        public string $default_check_out_time = '10:00:00',
    ) {}
}
