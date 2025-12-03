<?php

namespace App\Rules\Booking;

use App\Models\Configuration\Season;
use Carbon\Carbon;

class MinPersonsRule implements BookingRule
{
    public function validate(Carbon $start, Carbon $end, ?Season $season, int $guestsCount = 1): void
    {
        if (! $season || ! $season->min_persons) {
            return;
        }

        if ($guestsCount < $season->min_persons) {
            throw new \Exception("Minimum number of persons for this period is {$season->min_persons}.");
        }
    }
}
