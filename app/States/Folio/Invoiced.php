<?php

namespace App\States\Folio;

class Invoiced extends FolioState
{
    public static string $name = 'invoiced';

    public function label(): string
    {
        return 'Vyfakturovaný';
    }

    public function color(): string
    {
        return 'green';
    }
}
