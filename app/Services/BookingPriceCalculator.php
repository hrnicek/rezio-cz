<?php

namespace App\Services;

use App\Data\PriceBreakdown;
use App\Enums\ServicePriceType;
use App\Models\Configuration\Service;
use Carbon\Carbon;

class BookingPriceCalculator
{
    public function __construct(private SeasonalPricingService $seasonalPricing) {}

    public function calculate(
        string $propertyId,
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
        $total = 0;
        $details = [];

        foreach ($selections as $selection) {
            // Support both old and new payload structure if necessary, or just standardize
            $serviceId = $selection['service_id'] ?? $selection['extra_id'] ?? null;
            if (! $serviceId) {
                continue;
            }

            $service = Service::find($serviceId);
            $quantity = (int) ($selection['quantity'] ?? 0);

            if (! $service || ! $service->is_active || $quantity <= 0) {
                continue;
            }

            // Price logic based on Enum
            // price_amount is in cents (integer)
            $unitPrice = $service->price_amount instanceof \App\Support\Money ? $service->price_amount->getAmount() : (int) $service->price_amount;
            $lineTotal = 0;

            if ($service->price_type === ServicePriceType::PerNight || $service->price_type === ServicePriceType::PerDay) {
                $lineTotal = $quantity * $nights * $unitPrice;
            } else {
                // Fixed, PerPerson, PerStay, etc. - typically just quantity * unit_price
                // Unless PerPerson needs guests count logic (which we don't have here easily, assume quantity covers it)
                $lineTotal = $quantity * $unitPrice;
            }

            $total += $lineTotal;
            $details[] = [
                'service_id' => $service->id,
                'name' => $service->name,
                'quantity' => $quantity,
                'price_per_unit' => $unitPrice,
                'price_type' => $service->price_type,
                'line_total' => $lineTotal,
            ];
        }

        return [$total, $details];
    }
}
