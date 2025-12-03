<?php

namespace App\Services;

use App\Models\Configuration\Season;
use App\Rules\Booking\BookingRule;
use Carbon\Carbon;

class BookingRules
{
    /**
     * @var BookingRule[]
     */
    protected array $rules = [];

    public function addRule(BookingRule $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    public function validate(Carbon $start, Carbon $end, ?Season $season, int $guestsCount = 1): void
    {
        foreach ($this->rules as $rule) {
            $rule->validate($start, $end, $season, $guestsCount);
        }
    }
}
