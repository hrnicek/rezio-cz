<?php

namespace App\Services;

use App\States\Booking\Cancelled;
use App\Models\Booking\Booking;
use App\Models\Property;
use Carbon\Carbon;

class RevenueMetricsService
{
    public function calculate(Carbon $startDate, Carbon $endDate, ?int $propertyId = null): array
    {
        $startDate = $startDate->copy()->startOfDay();
        $endDate = $endDate->copy()->endOfDay();

        $bookingsQuery = Booking::query()
            ->where('status', '!=', Cancelled::class)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in_date', [$startDate, $endDate])
                    ->orWhereBetween('check_out_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('check_in_date', '<=', $startDate)
                            ->where('check_out_date', '>=', $endDate);
                    });
            })
            ->when($propertyId, function ($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            });

        $bookings = $bookingsQuery->get();

        // 1. Total Bookings Count
        $totalBookings = $bookings->count();

        // 2. Calculate Occupancy & Revenue (prorated per day)
        $totalDays = $startDate->diffInDays($endDate->copy()->startOfDay()) + 1;
        $totalUnits = $propertyId ? 1 : Property::count();
        $totalPossibleNights = $totalDays * $totalUnits;

        $bookedNights = 0;
        $totalRevenueCents = 0.0;
        $chartData = [];

        // Initialize chart data
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->format('Y-m-d');
            $chartData[$dateStr] = [
                'date' => $dateStr,
                'revenue' => 0,
                'occupancy' => 0,
            ];
            $current->addDay();
        }

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->check_in_date)->startOfDay();
            $bookingEnd = Carbon::parse($booking->check_out_date)->startOfDay();

            // Calculate daily rate for this booking (in cents)
            $bookingNights = $bookingStart->diffInDays($bookingEnd);
            $dailyRateCents = $bookingNights > 0 ? $booking->total_price_amount / $bookingNights : $booking->total_price_amount;

            // Iterate through each night of the booking
            $night = $bookingStart->copy();
            while ($night->lt($bookingEnd)) {
                // If this night falls within our reporting range
                if ($night->between($startDate, $endDate->copy()->subDay()->endOfDay())) {
                    $bookedNights++;
                    $totalRevenueCents += $dailyRateCents;

                    $dateStr = $night->format('Y-m-d');
                    if (isset($chartData[$dateStr])) {
                        $chartData[$dateStr]['revenue'] += round($dailyRateCents / 100, 2);
                        $chartData[$dateStr]['occupancy'] += 1;
                    }
                }
                $night->addDay();
            }
        }

        // 3. Calculate Key Metrics
        $occupancyRate = $totalPossibleNights > 0 ? round(($bookedNights / $totalPossibleNights) * 100, 1) : 0;
        $totalRevenue = round($totalRevenueCents / 100, 2);

        // ADR (Average Daily Rate) = Revenue / Sold Nights
        $adr = $bookedNights > 0 ? round($totalRevenue / $bookedNights, 2) : 0;

        // RevPAR (Revenue Per Available Room) = Revenue / Available Nights
        $revPar = $totalPossibleNights > 0 ? round($totalRevenue / $totalPossibleNights, 2) : 0;

        return [
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'occupancy_rate' => $occupancyRate,
            'adr' => $adr,
            'revpar' => $revPar,
            'booked_nights' => $bookedNights,
            'total_possible_nights' => $totalPossibleNights,
            'chart_data' => array_values($chartData),
        ];
    }
}
