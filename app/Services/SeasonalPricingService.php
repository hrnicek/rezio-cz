<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Season;
use Carbon\Carbon;

class SeasonalPricingService
{
    public function calculate_stay_price(int $propertyId, $checkInDate, $checkOutDate): float
    {
        $checkIn = $checkInDate instanceof Carbon ? $checkInDate->copy()->startOfDay() : Carbon::parse($checkInDate)->startOfDay();
        $checkOut = $checkOutDate instanceof Carbon ? $checkOutDate->copy()->startOfDay() : Carbon::parse($checkOutDate)->startOfDay();

        if ($checkOut->lte($checkIn)) {
            return 0.0;
        }

        $property = Property::query()->findOrFail($propertyId); // Keep this line to get property for base price fallback

        $seasons = Season::query()
            ->where('property_id', $propertyId)
            ->get();

        $defaultSeason = $seasons->firstWhere('is_default', true);
        $basePrice = $defaultSeason ? (float) $defaultSeason->price : (float) ($property->base_price ?? $property->price_per_night ?? 0);

        $priceByDate = [];

        // Iterate through each day of the requested stay
        $current = $checkIn->copy();
        while ($current->lt($checkOut)) {
            $dateKey = $current->toDateString();
            $price = $basePrice;

            // Find a matching specific season for this date
            // Priority: Higher priority wins.
            // We sort seasons by priority descending, so the first match is the best one.

            $matchingSeason = $seasons->sortByDesc('priority')->first(function (Season $season) use ($current) {
                return $season->matchesDate($current);
            });

            if ($matchingSeason) {
                $price = (float) $matchingSeason->price;
            }

            $priceByDate[$dateKey] = $price;
            $current->addDay();
        }

        $total = array_sum($priceByDate);

        return $total;
    }

    public function getSeasonForDate(int $propertyId, $date): ?Season
    {
        $date = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();

        $seasons = Season::query()
            ->where('property_id', $propertyId)
            ->get();

        // Find a matching specific season for this date
        // Priority: Higher priority wins.
        // We sort seasons by priority descending, so the first match is the best one.

        return $seasons->sortByDesc('priority')->first(function (Season $season) use ($date) {
            return $season->matchesDate($date);
        });
    }

    public function getPriceForDate(int $propertyId, $date): float
    {
        $date = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();

        $property = Property::query()->findOrFail($propertyId);
        
        // We need to fetch seasons again or pass them, but for now let's keep it simple and efficient enough
        // Actually, getSeasonForDate fetches seasons. To avoid N+1 if called in loop, we might want to optimize later,
        // but for now let's reuse the logic or just duplicate the fetch if needed, 
        // OR better: let's just use getSeasonForDate.
        
        // Optimization: getSeasonForDate fetches seasons. 
        // If we want to be optimal in a loop, we should probably fetch seasons once outside.
        // But the current signature of getPriceForDate takes propertyId.
        
        $matchingSeason = $this->getSeasonForDate($propertyId, $date);

        if ($matchingSeason) {
            return (float) $matchingSeason->price;
        }

        // Fallback to default season or property base price
        // We need to fetch seasons to find default if getSeasonForDate didn't return one (it returns specific match)
        // Wait, getSeasonForDate logic in my head: does it return default?
        // The original logic:
        // $matchingSeason = ... matchesDate ...
        // if ($matchingSeason) price = ...
        
        // Let's look at matchesDate in Season.php:
        // if ($this->is_default) return false; 
        // So matchesDate explicitly excludes default season.
        
        // So we need to handle default season separately.
        
        $seasons = Season::query()
            ->where('property_id', $propertyId)
            ->get();
            
        $defaultSeason = $seasons->firstWhere('is_default', true);
        return $defaultSeason ? (float) $defaultSeason->price : (float) ($property->base_price ?? $property->price_per_night ?? 0);
    }
}
