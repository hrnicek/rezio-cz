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
}
