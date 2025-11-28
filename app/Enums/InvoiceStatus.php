<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Návrh',
            self::Issued => 'Vystaveno',
            self::Paid => 'Zaplaceno',
            self::Cancelled => 'Zrušeno',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Issued => 'blue',
            self::Paid => 'green',
            self::Cancelled => 'red',
        };
    }
}
