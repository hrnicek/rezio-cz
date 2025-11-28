<?php

namespace App\Data;

class PriceBreakdown
{
    public function __construct(
        public readonly int $accommodation, // in cents
        public readonly int $services,      // in cents
        public readonly int $total,         // in cents
        public readonly array $serviceDetails = []
    ) {}
}
