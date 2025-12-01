<?php

namespace App\States\Folio;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class FolioState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Open::class)
            ->allowTransition(Open::class, Closed::class)
            ->allowTransition(Closed::class, Invoiced::class)
            ->allowTransition(Closed::class, Open::class) // Reopen
            ->allowTransition(Invoiced::class, Open::class) // Reopen (with caution)
            ->registerState([
                Open::class,
                Closed::class,
                Invoiced::class,
            ]);
    }
}
