<?php

namespace App\States\Invoice;

class Paid extends InvoiceState
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