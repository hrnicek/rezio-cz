<?php

namespace App\Enums;

enum BookingItemType: string
{
    case Night = 'night';
    case Service = 'service';
    case Fee = 'fee';

    public function label(): string
    {
        return match ($this) {
            self::Night => 'Noc',
            self::Service => 'Služba',
            self::Fee => 'Poplatek',
        };
    }
}
