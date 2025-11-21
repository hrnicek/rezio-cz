<?php

namespace App\Services;

use App\Data\PriceBreakdown;
use App\Models\Season;
use App\Models\Service;
use Carbon\Carbon;

class BookingPriceCalculator
{
    public function calculate(
        Carbon $startDate,
        Carbon $endDate,
        array $serviceSelections = []
    ): PriceBreakdown {
        $nights = max(1, $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()));

        // Calculate accommodation price from seasons
        $accommodationPrice = $this->calculateAccommodationPrice($startDate, $endDate, $nights);

        // Calculate services price
        [$servicesPrice, $serviceDetails] = $this->calculateServicesPrice($serviceSelections, $nights);

        return new PriceBreakdown(
            accommodation: $accommodationPrice,
            services: $servicesPrice,
            total: $accommodationPrice + $servicesPrice,
            serviceDetails: $serviceDetails
        );
    }

    private function calculateAccommodationPrice(Carbon $start, Carbon $end, int $nights): float
    {
        $total = 0.0;
        $current = $start->copy();

        while ($current->lt($end)) {
            $season = $this->getSeasonForDate($current);
            $total += $season?->price ?? 0;
            $current->addDay();
        }

        return $total;
    }

    private function calculateServicesPrice(array $selections, int $nights): array
    {
        $total = 0.0;
        $details = [];

        foreach ($selections as $selection) {
            $service = Service::find($selection['extra_id'] ?? $selection['service_id'] ?? null);
            $quantity = (int) ($selection['quantity'] ?? 0);

            if (!$service || !$service->is_active || $quantity <= 0) {
                continue;
            }

            $lineTotal = $service->price_type === 'per_day'
                ? $quantity * $nights * (float) $service->price
                : $quantity * (float) $service->price;

            $total += $lineTotal;
            $details[] = [
                'service_id' => $service->id,
                'name' => $service->name,
                'quantity' => $quantity,
                'price_per_unit' => (float) $service->price,
                'price_type' => $service->price_type,
                'line_total' => $lineTotal,
            ];
        }

        return [$total, $details];
    }

    private function getSeasonForDate(Carbon $date): ?Season
    {
        // Find matching season
        $seasons = Season::all();

        // Find custom season
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
