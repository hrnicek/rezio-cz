<?php

namespace App\Rules\Booking;

use Carbon\Carbon;
use App\Models\Season;

interface BookingRule
{
    public function validate(Carbon $start, Carbon $end, ?Season $season): void;
}
