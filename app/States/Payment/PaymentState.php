<?php

namespace App\States\Payment;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class PaymentState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Paid::class)
            ->allowTransition(Pending::class, Failed::class)
            ->allowTransition(Pending::class, Cancelled::class)
            ->allowTransition(Paid::class, Refunded::class)
            ->allowTransition(Failed::class, Pending::class)
            ->allowTransition(Cancelled::class, Pending::class)
            ->registerState([
                Pending::class,
                Paid::class,
                Failed::class,
                Cancelled::class,
                Refunded::class,
            ]);
    }
}
