<?php

namespace App\Actions\Booking;

use App\Models\Configuration\Season;
use Carbon\Carbon;

class CalculateDominantSeasonAction
{
    public function execute(Carbon $startDate, Carbon $endDate): ?Season
    {
        $seasons = Season::all();
        $seasonNights = [];

        $current = $startDate->copy();
        while ($current->lt($endDate)) {
            $season = $this->getSeasonForDate($current, $seasons);
            if ($season) {
                $seasonNights[$season->id] = ($seasonNights[$season->id] ?? 0) + 1;
            }
            $current->addDay();
        }

        if (empty($seasonNights)) {
            return null;
        }

        arsort($seasonNights);
        $dominantSeasonId = array_key_first($seasonNights);

        return Season::find($dominantSeasonId);
    }

    private function getSeasonForDate(Carbon $date, $seasons): ?Season
    {
        // Sort by priority descending to respect season priority
        $customSeason = $seasons->sortByDesc('priority')->first(function (Season $s) use ($date) {
            return $s->matchesDate($date);
        });

        return $customSeason ?: $seasons->firstWhere('is_default', true);
    }
}
