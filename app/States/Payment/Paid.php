<?php

namespace App\States\Payment;

class Paid extends PaymentState
{
    public static string $name = 'paid';

    public function label(): string
    {
        return 'Zaplaceno';
    }

    public function color(): string
    {
        return 'green';
    }
}
