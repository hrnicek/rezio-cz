<?php

namespace App\States\Invoice;

class Draft extends InvoiceState
{
    public static string $name = 'draft';

    public function label(): string
    {
        return 'Návrh';
    }

    public function color(): string
    {
        return 'gray';
    }
}