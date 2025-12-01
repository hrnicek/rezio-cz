<?php

namespace App\States\Payment;

class Refunded extends PaymentState
{
    public static string $name = 'refunded';

    public function label(): string
    {
        return 'Vráceno';
    }

    public function color(): string
    {
        return 'blue';
    }
}
