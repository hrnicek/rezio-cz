<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Card = 'card';
    case Bank = 'bank';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'Kartou',
            self::Bank => 'Převodem',
            self::Cash => 'Hotově',
        };
    }
}
