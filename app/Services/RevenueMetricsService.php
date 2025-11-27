<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use Carbon\Carbon;

class RevenueMetricsService
{
    public function calculate(Carbon $startDate, Carbon $endDate, ?int $propertyId = null): array
    {
        $startDate = $startDate->copy()->startOfDay();
        $endDate = $endDate->copy()->endOfDay();

        $bookingsQuery = Booking::query()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
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
        $totalRevenue = 0.0;
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
            $bookingStart = Carbon::parse($booking->start_date)->startOfDay();
            $bookingEnd = Carbon::parse($booking->end_date)->startOfDay();

            // Calculate daily rate for this booking
            $bookingNights = $bookingStart->diffInDays($bookingEnd);
            $dailyRate = $bookingNights > 0 ? $booking->total_price / $bookingNights : $booking->total_price;

            // Iterate through each night of the booking
            $night = $bookingStart->copy();
            while ($night->lt($bookingEnd)) {
                // If this night falls within our reporting range
                if ($night->between($startDate, $endDate->copy()->subDay()->endOfDay())) {
                    $bookedNights++;
                    $totalRevenue += $dailyRate;

                    $dateStr = $night->format('Y-m-d');
                    if (isset($chartData[$dateStr])) {
                        $chartData[$dateStr]['revenue'] += $dailyRate;
                        $chartData[$dateStr]['occupancy'] += 1;
                    }
                }
                $night->addDay();
            }
        }

        // 3. Calculate Key Metrics
        $occupancyRate = $totalPossibleNights > 0 ? round(($bookedNights / $totalPossibleNights) * 100, 1) : 0;

        // ADR (Average Daily Rate) = Revenue / Sold Nights
        $adr = $bookedNights > 0 ? round($totalRevenue / $bookedNights, 2) : 0;

        // RevPAR (Revenue Per Available Room) = Revenue / Available Nights
        $revPar = $totalPossibleNights > 0 ? round($totalRevenue / $totalPossibleNights, 2) : 0;

        return [
            'total_bookings' => $totalBookings,
            'total_revenue' => round($totalRevenue, 2),
            'occupancy_rate' => $occupancyRate,
            'adr' => $adr,
            'revpar' => $revPar,
            'booked_nights' => $bookedNights,
            'total_possible_nights' => $totalPossibleNights,
            'chart_data' => array_values($chartData),
        ];
    }
}
