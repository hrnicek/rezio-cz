<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Čekající',
            self::Confirmed => 'Potvrzeno',
            self::CheckedIn => 'Ubytován',
            self::CheckedOut => 'Odhlášen',
            self::Cancelled => 'Zrušeno',
            self::NoShow => 'Nedostavil se',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Confirmed => 'green',
            self::CheckedIn => 'blue',
            self::CheckedOut => 'gray',
            self::Cancelled => 'red',
            self::NoShow => 'orange',
        };
    }
}
