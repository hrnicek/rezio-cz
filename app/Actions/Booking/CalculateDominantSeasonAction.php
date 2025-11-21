<?php

namespace App\Actions\Booking;

use App\Models\Season;
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
        $customSeason = $seasons->first(function (Season $s) use ($date) {
            $md = $date->format('m-d');
            $startMd = $s->start_date->format('m-d');
            $endMd = $s->end_date->format('m-d');

            if ($startMd <= $endMd) {
                return !$s->is_default && $md >= $startMd && $md <= $endMd;
            }

            return !$s->is_default && ($md >= $startMd || $md <= $endMd);
        });

        return $customSeason ?: $seasons->firstWhere('is_default', true);
    }
}
