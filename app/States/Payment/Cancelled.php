<?php

namespace App\States\Payment;

class Cancelled extends PaymentState
{
    public static string $name = 'cancelled';

    public function label(): string
    {
        return 'Zrušeno';
    }

    public function color(): string
    {
        return 'gray';
    }
}
