<?php

namespace App\Data\Shared;

use App\Support\Money;
use Spatie\LaravelData\Data;

class MoneyData extends Data
{
    public function __construct(
        public int $amount,        // 14000 (haléře)
        public string $currency,   // CZK
        public float $value,       // 140.00 (pro frontend - raw value)
        public string $formatted,  // "140 Kč" (pro zobrazení - formatted string)
    ) {}

    // Factory metoda pro rychlé vytvoření z modelu
    public static function fromModel(int|Money $amount, string $currency = 'CZK'): self
    {
        if ($amount instanceof Money) {
            return new self(
                amount: (int) $amount->getAmount(),
                currency: $amount->getCurrency()->getCurrency(),
                value: $amount->getValue(),
                formatted: $amount->format()
            );
        }

        // Fallback pro int (starý způsob, pokud se někde ještě používá raw int)
        return new self(
            amount: $amount,
            currency: $currency,
            value: $amount / 100,
            formatted: number_format($amount / 100, 0, ',', ' ').' '.$currency
        );
    }
}
