<?php

namespace App\States\Invoice;

class Issued extends InvoiceState
{
    public static string $name = 'issued';

    public function label(): string
    {
        return 'Vystaveno';
    }

    public function color(): string
    {
        return 'blue';
    }
}