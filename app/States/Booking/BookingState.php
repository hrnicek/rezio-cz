<?php

namespace App\States\Booking;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class BookingState extends State
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Confirmed::class)
            ->allowTransition(Pending::class, Cancelled::class)
            ->allowTransition(Pending::class, Paid::class)
            ->allowTransition(Confirmed::class, Completed::class)
            ->allowTransition(Confirmed::class, Cancelled::class)
            ->allowTransition(Confirmed::class, Paid::class)
            ->allowTransition(Paid::class, Completed::class)
            ->allowTransition(Paid::class, Cancelled::class);
    }
}
