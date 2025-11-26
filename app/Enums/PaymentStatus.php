<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Čekající',
            self::Paid => 'Zaplaceno',
            self::Refunded => 'Vráceno',
            self::Cancelled => 'Zrušeno',
            self::Failed => 'Selhalo',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Paid => 'green',
            self::Refunded => 'blue',
            self::Cancelled => 'gray',
            self::Failed => 'red',
        };
    }
}
