<?php

namespace App\Http\Controllers\Client\Api\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $month = (int) ($validated['month'] ?? now()->month);
        $year = (int) ($validated['year'] ?? now()->year);

        $property = Property::where('widget_token', $token)->firstOrFail();

        $periodStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $bookings = Booking::query()
            ->where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('start_date', [$periodStart, $periodEnd])
                    ->orWhereBetween('end_date', [$periodStart, $periodEnd])
                    ->orWhere(function ($query) use ($periodStart, $periodEnd) {
                        $query->where('start_date', '<=', $periodStart)
                            ->where('end_date', '>=', $periodEnd);
                    });
            })
            ->get();

        $seasonalPrices = $property->seasonalPrices()
            ->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('start_date', [$periodStart, $periodEnd])
                    ->orWhereBetween('end_date', [$periodStart, $periodEnd])
                    ->orWhere(function ($query) use ($periodStart, $periodEnd) {
                        $query->where('start_date', '<=', $periodStart)
                            ->where('end_date', '>=', $periodEnd);
                    });
            })
            ->get();

        $days = [];
        $date = $periodStart->copy();
        $earliest = now()->startOfDay();

        while ($date->lte($periodEnd)) {
            $isBooked = $bookings->contains(function (Booking $b) use ($date) {
                $bookingStart = \Carbon\Carbon::parse($b->start_date)->startOfDay();
                $bookingEndExclusive = \Carbon\Carbon::parse($b->end_date)->subDay()->startOfDay();
                return $date->between($bookingStart, $bookingEndExclusive);
            });

            $meetsLead = $date->gte($earliest);

            $price = $property->price_per_night;
            foreach ($seasonalPrices as $season) {
                if ($date->between($season->start_date, $season->end_date)) {
                    $price = $season->price_per_night;
                    break;
                }
            }

            $days[] = [
                'date' => $date->toDateString(),
                'available' => $meetsLead && ! $isBooked,
                'price' => $price,
            ];

            $date = $date->addDay();
        }

        return response()->json([
            'month' => $month,
            'year' => $year,
            'days' => $days,
        ]);
    }
}
