<?php

namespace App\States\Payment;

class Failed extends PaymentState
{
    public static string $name = 'failed';

    public function label(): string
    {
        return 'Selhalo';
    }

    public function color(): string
    {
        return 'red';
    }
}
