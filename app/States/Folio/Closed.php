<?php

namespace App\States\Folio;

class Closed extends FolioState
{
    public static string $name = 'closed';

    public function label(): string
    {
        return 'Uzavřený';
    }

    public function color(): string
    {
        return 'gray';
    }
}
