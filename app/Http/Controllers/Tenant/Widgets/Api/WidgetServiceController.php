<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Property;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Service\CheckAvailabilityRequest;

class WidgetServiceController extends Controller
{
    public function index(Property $property): JsonResponse
    {
        $services = Service::query()
            ->where('property_id', $property->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price_type', 'price', 'max_quantity']);

        return response()->json([
            'services' => $services,
        ]);
    }

    public function availability(CheckAvailabilityRequest $request, Property $property): JsonResponse
    {
        $data = $request->validated();

        $checkin = config('booking.checkin_time', '14:00');
        $checkout = config('booking.checkout_time', '10:00');
        $start = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'] . ' ' . $checkin);
        $end = Carbon::createFromFormat('Y-m-d H:i', $data['end_date'] . ' ' . $checkout);

        $overlappingBookings = Booking::query()
            ->where('status', '!=', 'cancelled')
            ->where('date_start', '<', $end)
            ->where('date_end', '>', $start)
            ->with(['services'])
            ->get();

        $resultItems = [];
        $overallAvailable = true;

        foreach ($data['selections'] as $selection) {
            $serviceId = $selection['service_id'] ?? $selection['extra_id'] ?? null;

            /** @var Service $service */
            $service = Service::query()->find($serviceId);
            if (! $service || ! $service->is_active) {
                $resultItems[] = [
                    'service_id' => $serviceId,
                    'extra_id' => $serviceId, // Backward compatibility
                    'available_quantity' => 0,
                    'requested_quantity' => (int) $selection['quantity'],
                    'is_available' => false,
                ];
                $overallAvailable = false;

                continue;
            }

            $bookedQty = 0;
            foreach ($overlappingBookings as $booking) {
                $pivot = $booking->services->firstWhere('id', $service->id)?->pivot;
                if ($pivot) {
                    $bookedQty += (int) $pivot->quantity;
                }
            }

            $availableQty = max(0, (int) $service->max_quantity - $bookedQty);
            $isAvailable = $availableQty >= (int) $selection['quantity'];
            $overallAvailable = $overallAvailable && $isAvailable;

            $resultItems[] = [
                'service_id' => $service->id,
                'extra_id' => $service->id, // Backward compatibility
                'available_quantity' => $availableQty,
                'requested_quantity' => (int) $selection['quantity'],
                'is_available' => $isAvailable,
            ];
        }

        return response()->json([
            'available' => $overallAvailable,
            'items' => $resultItems,
        ]);
    }

    public function availabilityByProperty(string $token, CheckAvailabilityRequest $request): JsonResponse
    {
        // For now, check availability regardless of property token
        // In the future, you could filter by specific property if needed
        return $this->availability($request);
    }
}
