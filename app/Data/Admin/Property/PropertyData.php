<?php

namespace App\Data\Admin\Property;

use App\Models\Property;
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

    public static function fromModel(Property $property): self
    {
        return new self(
            id: $property->id,
            name: $property->name,
            address: $property->address,
            image: $property->image ? asset($property->image) : null,
            description: $property->description,
            default_check_in_time: $property->default_check_in_time ?? '15:00:00',
            default_check_out_time: $property->default_check_out_time ?? '10:00:00',
        );
    }
}
