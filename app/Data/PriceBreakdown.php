<?php

namespace App\Data;

class PriceBreakdown
{
    public function __construct(
        public readonly float $accommodation,
        public readonly float $services,
        public readonly float $total,
        public readonly array $serviceDetails = []
    ) {}
}
