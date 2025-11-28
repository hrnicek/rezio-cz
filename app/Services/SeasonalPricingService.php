<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Configuration\Season;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SeasonalPricingService
{
    private ?Collection $seasonsCache = null;
    private ?int $cachedPropertyId = null;

    private function loadSeasons(int $propertyId): Collection
    {
        if ($this->seasonsCache === null || $this->cachedPropertyId !== $propertyId) {
            $this->seasonsCache = Season::query()
                ->where('property_id', $propertyId)
                ->get();
            $this->cachedPropertyId = $propertyId;
        }

        return $this->seasonsCache;
    }

    public function getSeasonForDate(int $propertyId, Carbon $date): ?Season
    {
        $seasons = $this->loadSeasons($propertyId);
        
        // Find specific season (High priority first)
        return $seasons->where('is_default', false)
            ->sortByDesc('priority')
            ->first(function (Season $season) use ($date) {
                if ($season->start_date && $season->end_date) {
                    return $date->between($season->start_date, $season->end_date);
                }
                return false;
            });
    }

    public function getPriceForDate(int $propertyId, Carbon $date): int
    {
        $seasons = $this->loadSeasons($propertyId);
        $defaultSeason = $seasons->firstWhere('is_default', true);
        $basePrice = $defaultSeason ? (int) $defaultSeason->price_amount : 0;

        $matchingSeason = $this->getSeasonForDate($propertyId, $date);

        return $matchingSeason ? (int) $matchingSeason->price_amount : $basePrice;
    }

    public function calculate_stay_price(int $propertyId, $checkInDate, $checkOutDate): int
    {
        $checkIn = $checkInDate instanceof Carbon ? $checkInDate->copy()->startOfDay() : Carbon::parse($checkInDate)->startOfDay();
        $checkOut = $checkOutDate instanceof Carbon ? $checkOutDate->copy()->startOfDay() : Carbon::parse($checkOutDate)->startOfDay();

        if ($checkOut->lte($checkIn)) {
            return 0;
        }

        $total = 0;
        $current = $checkIn->copy();

        while ($current->lt($checkOut)) {
            $total += $this->getPriceForDate($propertyId, $current);
            $current->addDay();
        }

        return $total;
    }
}
