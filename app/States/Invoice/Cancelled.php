<?php

namespace App\States\Invoice;

class Cancelled extends InvoiceState
{
    public static string $name = 'cancelled';

    public function label(): string
    {
        return 'Stornováno';
    }

    public function color(): string
    {
        return 'red';
    }
}