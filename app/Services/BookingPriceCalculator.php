<?php

namespace App\Services;

use App\Data\PriceBreakdown;
use App\Models\Service;
use Carbon\Carbon;

class BookingPriceCalculator
{
    public function __construct(private SeasonalPricingService $seasonalPricing) {}

    public function calculate(
        int $propertyId,
        Carbon $startDate,
        Carbon $endDate,
        array $serviceSelections = []
    ): PriceBreakdown {
        $nights = max(1, $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()));

        $accommodationPrice = $this->seasonalPricing->calculate_stay_price($propertyId, $startDate, $endDate);

        [$servicesPrice, $serviceDetails] = $this->calculateServicesPrice($serviceSelections, $nights);

        return new PriceBreakdown(
            accommodation: $accommodationPrice,
            services: $servicesPrice,
            total: $accommodationPrice + $servicesPrice,
            serviceDetails: $serviceDetails
        );
    }

    private function calculateServicesPrice(array $selections, int $nights): array
    {
        $total = 0.0;
        $details = [];

        foreach ($selections as $selection) {
            $service = Service::find($selection['extra_id'] ?? $selection['service_id'] ?? null);
            $quantity = (int) ($selection['quantity'] ?? 0);

            if (! $service || ! $service->is_active || $quantity <= 0) {
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

    // Season rules are handled elsewhere; pricing is delegated to SeasonalPricingService
}
