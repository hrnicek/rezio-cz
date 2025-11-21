<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyAvailabilityController extends Controller
{
    public function __invoke(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'selections' => ['nullable', 'array'],
            'selections.*.service_id' => ['required_with:selections', 'integer', 'exists:services,id'],
            'selections.*.quantity' => ['required_with:selections', 'integer', 'min:1'],
        ]);

        $property = Property::where('widget_token', $token)->firstOrFail();

        $start = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
        $end = \Carbon\Carbon::parse($validated['end_date'])->startOfDay();

        $bookings = Booking::query()
            ->where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                    });
            })
            ->get();

        $unavailableDates = [];
        $cursor = $start->copy();
        $cursorEnd = $end->copy();

        while ($cursor->lte($cursorEnd)) {
            $isBooked = $bookings->contains(function (Booking $b) use ($cursor) {
                $bookingStart = \Carbon\Carbon::parse($b->start_date)->startOfDay();
                $bookingEndExclusive = \Carbon\Carbon::parse($b->end_date)->subDay()->startOfDay();
                return $cursor->between($bookingStart, $bookingEndExclusive);
            });

            if ($isBooked) {
                $unavailableDates[] = $cursor->toDateString();
            }

            $cursor = $cursor->addDay();
        }

        $dateAvailable = count($unavailableDates) === 0;

        $servicesAvailable = true;
        $items = [];

        if (! empty($validated['selections'])) {
            $overlapping = Booking::query()
                ->where('property_id', $property->id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('start_date', '<', $start)
                                ->where('end_date', '>', $end);
                        });
                })
                ->with('services')
                ->get();

            foreach ($validated['selections'] as $sel) {
                $service = \App\Models\Service::query()->find($sel['service_id']);
                if (! $service || ! $service->is_active) {
                    $items[] = [
                        'service_id' => $sel['service_id'],
                        'available_quantity' => 0,
                        'requested_quantity' => (int) $sel['quantity'],
                        'is_available' => false,
                    ];
                    $servicesAvailable = false;
                    continue;
                }

                $bookedQty = 0;
                foreach ($overlapping as $b) {
                    $pivot = $b->services->firstWhere('id', $service->id)?->pivot;
                    if ($pivot) {
                        $bookedQty += (int) $pivot->quantity;
                    }
                }

                $availableQty = max(0, (int) $service->max_quantity - $bookedQty);
                $isAvailable = $availableQty >= (int) $sel['quantity'];
                $servicesAvailable = $servicesAvailable && $isAvailable;

                $items[] = [
                    'service_id' => $service->id,
                    'available_quantity' => $availableQty,
                    'requested_quantity' => (int) $sel['quantity'],
                    'is_available' => $isAvailable,
                ];
            }
        }

        return response()->json([
            'available' => $dateAvailable && $servicesAvailable,
            'unavailable_dates' => $unavailableDates,
            'services' => empty($validated['selections']) ? null : [
                'available' => $servicesAvailable,
                'items' => $items,
            ],
        ]);
    }
}
