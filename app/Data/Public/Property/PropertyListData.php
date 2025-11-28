<?php

namespace App\Data\Public\Property;

use App\Data\Shared\MoneyData;
use Spatie\LaravelData\Data;

class PropertyListData extends Data
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $image,
        public ?string $short_description,
        public ?MoneyData $starting_price,
    ) {}
}
