<?php

namespace App\Services;

use App\Models\Configuration\Season;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SeasonalPricingService
{
    private ?Collection $seasonsCache = null;

    private ?string $cachedPropertyId = null;

    private function loadSeasons(string $propertyId): Collection
    {
        if ($this->seasonsCache === null || $this->cachedPropertyId !== $propertyId) {
            $this->seasonsCache = Season::query()
                ->where('property_id', $propertyId)
                ->get();
            $this->cachedPropertyId = $propertyId;
        }

        return $this->seasonsCache;
    }

    public function getSeasonForDate(string $propertyId, Carbon $date): ?Season
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

    private function getPriceAmount(mixed $price): int
    {
        if ($price instanceof \App\Support\Money) {
            return (int) $price->getAmount();
        }

        return (int) $price;
    }

    public function getPriceForDate(string $propertyId, Carbon $date): int
    {
        $seasons = $this->loadSeasons($propertyId);
        $defaultSeason = $seasons->firstWhere('is_default', true);
        $basePrice = $defaultSeason ? $this->getPriceAmount($defaultSeason->price_amount) : 0;

        $matchingSeason = $this->getSeasonForDate($propertyId, $date);

        return $matchingSeason ? $this->getPriceAmount($matchingSeason->price_amount) : $basePrice;
    }

    public function calculate_stay_price(string $propertyId, $checkInDate, $checkOutDate): int
    {
        $checkIn = $checkInDate instanceof Carbon ? $checkInDate->copy()->startOfDay() : \Illuminate\Support\Facades\Date::parse($checkInDate)->startOfDay();
        $checkOut = $checkOutDate instanceof Carbon ? $checkOutDate->copy()->startOfDay() : \Illuminate\Support\Facades\Date::parse($checkOutDate)->startOfDay();

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
