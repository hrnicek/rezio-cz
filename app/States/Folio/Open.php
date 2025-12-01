<?php

namespace App\States\Folio;

class Open extends FolioState
{
    public static string $name = 'open';

    public function label(): string
    {
        return 'Otevřený';
    }

    public function color(): string
    {
        return 'blue';
    }
}
