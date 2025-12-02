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

        // If value is float/int (e.g. from form input 60.00),
        // we usually want to convert to cents IF it's not already cents.
        // BUT, standard Laravel behavior is: set(6000) -> 6000.
        // set(60.00) -> 60.
        // If the user types 60 in the form, and we send 60 to the backend.
        // If we want 60 CZK, we need 6000.
        // However, the CAST is usually dumb. It assumes the input to the setter is already what you want (or close to it).
        // The Akaunting Money package constructor takes amount in SUBUNITS (cents).
        // So if I do new Money(60, 'CZK'), I get 0.60 CZK.
        // So if I want 60 CZK, I must provide 6000.

        // For the setter:
        // If I do $model->price = 6000; -> I expect 60 CZK.
        // If I do $model->price = 60; -> I expect 0.60 CZK.
        // So I will just cast to int.

        return (int) $value;
    }
}
