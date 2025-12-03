<?php

namespace App\Casts;

use Akaunting\Money\Currency;
use App\Support\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MoneyCast implements CastsAttributes
{
    protected string $currency;

    public function __construct(string $currency = 'CZK')
    {
        $this->currency = $currency;
    }

    public function get($model, string $key, $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        // If model has a currency attribute, use it
        $currency = $attributes['currency'] ?? $this->currency;

        return new Money($value, new Currency($currency));
    }

    public function set($model, string $key, $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Money) {
            return $value->getAmount();
        }

        if (is_int($value)) {
            return $value;
        }

        // Strict check: Reject floats to prevent precision issues
        if (is_float($value)) {
            throw new \InvalidArgumentException(
                "MoneyCast: Float values are not allowed to prevent precision loss. " .
                "Passed: {$value}. Please pass an integer (cents) or a Money object."
            );
        }

        // Strict check: Reject strings unless they are purely numeric integers
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        throw new \InvalidArgumentException(
            "MoneyCast: Invalid type. Expected Money object or integer (cents). " .
            "Received: " . gettype($value)
        );
    }
}
