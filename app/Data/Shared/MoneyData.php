<?php

namespace App\Data\Shared;

use Spatie\LaravelData\Data;

class MoneyData extends Data
{
    public function __construct(
        public int $amount,        // 14000 (haléře)
        public string $currency,   // CZK
        public float $formatted,   // 140.00 (pro frontend)
        public string $display,    // "140 Kč" (pro zobrazení)
    ) {}

    // Factory metoda pro rychlé vytvoření z modelu
    public static function fromModel(int $amount, string $currency = 'CZK'): self
    {
        return new self(
            amount: $amount,
            currency: $currency,
            formatted: $amount / 100,
            display: number_format($amount / 100, 0, ',', ' ').' '.$currency
        );
    }
}
