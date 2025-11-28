<?php

namespace App\Enums;

enum FolioStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Invoiced = 'invoiced';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Otevřený',
            self::Closed => 'Uzavřený',
            self::Invoiced => 'Vyfakturovaný',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'blue',
            self::Closed => 'gray',
            self::Invoiced => 'green',
        };
    }
}
