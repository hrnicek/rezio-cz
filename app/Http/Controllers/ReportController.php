<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Reports/Index', [
            'properties' => \App\Models\Property::select('id', 'name')->get(),
        ]);
    }

    public function data(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
        $propertyId = $request->property_id;

        $bookingsQuery = \App\Models\Booking::query()
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

        // Calculate Revenue
        $totalRevenue = $bookings->sum('total_price');

        // Calculate Occupancy
        // Use startOfDay for both to get accurate day count
        $totalDays = $startDate->diffInDays($endDate->copy()->startOfDay()) + 1;
        $totalPossibleNights = $totalDays * ($propertyId ? 1 : \App\Models\Property::count());

        // Calculate booked nights within the range
        $bookedNights = 0;
        foreach ($bookings as $booking) {
            $bookingStart = \Carbon\Carbon::parse($booking->start_date);
            $bookingEnd = \Carbon\Carbon::parse($booking->end_date);

            // Clamp to range
            $effectiveStart = $bookingStart->max($startDate);
            $effectiveEnd = $bookingEnd->min($endDate);

            // We need to compare days, ignoring time
            $effectiveStart = $effectiveStart->copy()->startOfDay();
            $effectiveEnd = $effectiveEnd->copy()->startOfDay();

            if ($effectiveStart->lt($effectiveEnd)) {
                $bookedNights += $effectiveStart->diffInDays($effectiveEnd);
            }
        }

        $occupancyRate = $totalPossibleNights > 0 ? round(($bookedNights / $totalPossibleNights) * 100, 1) : 0;


        // Prepare Chart Data (Daily Revenue)
        $chartData = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->format('Y-m-d');
            $dailyRevenue = 0;

            // This is a simplified daily revenue calculation (allocating full booking price to start date or splitting it?)
            // For better accuracy, we should split price per night. 
            // For now, let's just sum up bookings that START on this day for "Sales" or check-in based revenue.
            // OR, better: Daily Revenue = Sum of (Price Per Night) for all active bookings on that day.
            // Let's do Daily Revenue based on active nights.

            foreach ($bookings as $booking) {
                $bStart = \Carbon\Carbon::parse($booking->start_date);
                $bEnd = \Carbon\Carbon::parse($booking->end_date);

                if ($current->between($bStart, $bEnd->copy()->subDay())) {
                    // Simple average daily rate for the booking
                    $nights = $bStart->diffInDays($bEnd);
                    $dailyRate = $nights > 0 ? $booking->total_price / $nights : $booking->total_price;
                    $dailyRevenue += $dailyRate;
                }
            }

            $chartData[] = [
                'date' => $dateStr,
                'revenue' => round($dailyRevenue, 2),
            ];
            $current->addDay();
        }

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_bookings' => $bookings->count(),
            'occupancy_rate' => $occupancyRate,
            'chart_data' => $chartData,
        ]);
    }
}
