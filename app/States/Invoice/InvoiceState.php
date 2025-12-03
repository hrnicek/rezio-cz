<?php

namespace App\States\Invoice;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class InvoiceState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Issued::class)
            ->allowTransition(Draft::class, Cancelled::class)
            ->allowTransition(Issued::class, Paid::class)
            ->allowTransition(Issued::class, Cancelled::class)
            ->registerState([
                Draft::class,
                Issued::class,
                Paid::class,
                Cancelled::class,
            ]);
    }
}