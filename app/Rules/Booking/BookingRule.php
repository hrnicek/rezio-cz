<?php

namespace App\Rules\Booking;

use App\Models\Configuration\Season;
use Carbon\Carbon;

interface BookingRule
{
    public function validate(Carbon $start, Carbon $end, ?Season $season): void;
}
