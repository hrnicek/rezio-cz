<?php

namespace App\Http\Controllers\Client\Api\Service;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckServiceAvailabilityController extends Controller
{
    public function __invoke(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'selections' => ['required', 'array'],
            'selections.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'selections.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $property = Property::where('widget_token', $token)->firstOrFail();

        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);

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

        $items = [];
        $overall = true;

        foreach ($validated['selections'] as $sel) {
            $service = Service::query()->find($sel['service_id']);
            if (! $service || ! $service->is_active) {
                $items[] = [
                    'service_id' => $sel['service_id'],
                    'available_quantity' => 0,
                    'requested_quantity' => (int) $sel['quantity'],
                    'is_available' => false,
                ];
                $overall = false;
                continue;
            }

            $bookedQty = 0;
            foreach ($overlapping as $booking) {
                $pivot = $booking->services->firstWhere('id', $service->id)?->pivot;
                if ($pivot) {
                    $bookedQty += (int) $pivot->quantity;
                }
            }

            $availableQty = max(0, (int) $service->max_quantity - $bookedQty);
            $isAvailable = $availableQty >= (int) $sel['quantity'];
            $overall = $overall && $isAvailable;

            $items[] = [
                'service_id' => $service->id,
                'available_quantity' => $availableQty,
                'requested_quantity' => (int) $sel['quantity'],
                'is_available' => $isAvailable,
            ];
        }

        return response()->json([
            'available' => $overall,
            'items' => $items,
        ]);
    }
}
