<?php

namespace App\States\Payment;

class Pending extends PaymentState
{
    public static string $name = 'pending';

    public function label(): string
    {
        return 'Čekající';
    }

    public function color(): string
    {
        return 'yellow';
    }
}
