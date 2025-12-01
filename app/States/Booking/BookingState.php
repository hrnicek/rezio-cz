<?php

namespace App\States\Booking;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class BookingState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Confirmed::class)
            ->allowTransition(Pending::class, Cancelled::class)
            ->allowTransition(Confirmed::class, CheckedIn::class)
            ->allowTransition(Confirmed::class, Cancelled::class)
            ->allowTransition(Confirmed::class, NoShow::class)
            ->allowTransition(CheckedIn::class, CheckedOut::class)
            ->allowTransition(CheckedIn::class, Confirmed::class) // Correction
            ->allowTransition(CheckedOut::class, CheckedIn::class) // Correction
            ->allowTransition(Cancelled::class, Pending::class) // Reopen
            ->allowTransition(NoShow::class, Pending::class) // Reopen
            ->allowTransition(NoShow::class, Confirmed::class) // Correction
            ->registerState([
                Pending::class,
                Confirmed::class,
                CheckedIn::class,
                CheckedOut::class,
                Cancelled::class,
                NoShow::class,
            ]);
    }
}
