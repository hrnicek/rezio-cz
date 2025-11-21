<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreBookingController extends Controller
{
    public function __invoke(Request $request, string $token): JsonResponse
    {
        $property = Property::where('widget_token', $token)->firstOrFail();

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'selections' => ['nullable', 'array'],
            'selections.*.service_id' => ['required_with:selections', 'integer', 'exists:services,id'],
            'selections.*.quantity' => ['required_with:selections', 'integer', 'min:1'],
        ]);

        $overlap = Booking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($query) use ($validated) {
                        $query->where('start_date', '<', $validated['start_date'])
                            ->where('end_date', '>', $validated['end_date']);
                    });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'Selected dates are not available.',
                'errors' => ['start_date' => ['Selected dates are not available.']],
            ], 422);
        }

        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);

        $totalPrice = $this->calculateTotalPrice($property, $start, $end);

        $overlapping = \App\Models\Booking::query()
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

        $nights = max(1, $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()));
        $serviceItems = [];
        $availabilityOk = true;

        foreach ($validated['selections'] ?? [] as $sel) {
            $service = \App\Models\Service::query()->find($sel['service_id']);
            if (! $service || ! $service->is_active) {
                $serviceItems[] = [
                    'service_id' => $sel['service_id'],
                    'available_quantity' => 0,
                    'requested_quantity' => (int) $sel['quantity'],
                    'is_available' => false,
                ];
                $availabilityOk = false;
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
            $availabilityOk = $availabilityOk && $isAvailable;

            $lineTotal = $service->price_type === 'per_day'
                ? (int) $sel['quantity'] * $nights * (float) $service->price
                : (int) $sel['quantity'] * (float) $service->price;

            $totalPrice += $lineTotal;

            $serviceItems[] = [
                'service_id' => $service->id,
                'available_quantity' => $availableQty,
                'requested_quantity' => (int) $sel['quantity'],
                'is_available' => $isAvailable,
                'line_total' => $lineTotal,
            ];
        }

        if (! $availabilityOk) {
            return response()->json([
                'message' => 'Selected services are not available in requested quantities.',
                'available' => false,
                'items' => $serviceItems,
            ], 422);
        }

        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $property->user_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'guest_info' => [
                'name' => $validated['guest_name'],
                'email' => $validated['guest_email'],
                'phone' => $validated['guest_phone'],
            ],
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        foreach ($serviceItems as $item) {
            $service = \App\Models\Service::find($item['service_id']);
            if ($service) {
                $booking->services()->attach($service->id, [
                    'quantity' => $item['requested_quantity'],
                    'price_total' => $item['line_total'] ?? 0,
                ]);
            }
        }

        \Illuminate\Support\Facades\Mail::to($validated['guest_email'])->send(new \App\Mail\BookingConfirmation($booking));
        \Illuminate\Support\Facades\Mail::to($property->user->email)->send(new \App\Mail\NewBookingAlert($booking));

        return response()->json([
            'message' => 'Booking request sent successfully.',
            'booking' => $booking->fresh()->load('services'),
        ], 201);
    }

    private function calculateTotalPrice(Property $property, \Carbon\Carbon $start, \Carbon\Carbon $end)
    {
        $seasonalPrices = $property->seasonalPrices()
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->get();

        $totalPrice = 0;
        $currentDate = $start->copy();

        while ($currentDate->lt($end)) {
            $price = $property->price_per_night;

            foreach ($seasonalPrices as $season) {
                if ($currentDate->between($season->start_date, $season->end_date)) {
                    $price = $season->price_per_night;
                    break;
                }
            }

            $totalPrice += $price;
            $currentDate->addDay();
        }

        return $totalPrice;
    }
}
