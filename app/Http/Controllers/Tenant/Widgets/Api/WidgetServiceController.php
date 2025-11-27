<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\CheckAvailabilityRequest;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class WidgetServiceController extends Controller
{
    public function index(Property $id): JsonResponse
    {
        $services = Service::query()
            ->where('property_id', $id->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price_type', 'price', 'max_quantity']);

        return response()->json([
            'services' => $services,
        ]);
    }

    public function availability(CheckAvailabilityRequest $request, Property $id): JsonResponse
    {
        $data = $request->validated();

        // Use property's updated_at timestamp for cache invalidation
        // When a booking is created/updated, it touches the property (via Booking::$touches)
        $timestamp = $id->updated_at?->timestamp ?? '0';
        $cacheKey = "widget_availability:{$id->id}:{$timestamp}:".md5(serialize($data));

        $result = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($data, $id) {
            $checkin = config('booking.checkin_time', '14:00');
            $checkout = config('booking.checkout_time', '10:00');
            $start = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'].' '.$checkin);
            $end = Carbon::createFromFormat('Y-m-d H:i', $data['end_date'].' '.$checkout);

            $overlappingBookings = Booking::query()
                ->where('property_id', $id->id)
                ->where('status', '!=', 'cancelled')
                ->where('date_start', '<', $end)
                ->where('date_end', '>', $start)
                ->with(['services'])
                ->get();

            // Pre-fetch services to avoid N+1
            $serviceIds = collect($data['selections'])
                ->map(fn ($s) => $s['service_id'] ?? $s['extra_id'] ?? null)
                ->filter()
                ->unique();

            $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

            $resultItems = [];
            $overallAvailable = true;

            foreach ($data['selections'] as $selection) {
                $serviceId = $selection['service_id'] ?? $selection['extra_id'] ?? null;
                $service = $services->get($serviceId);

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

            return [
                'available' => $overallAvailable,
                'items' => $resultItems,
            ];
        });

        return response()->json($result);
    }

    public function availabilityByProperty(string $token, CheckAvailabilityRequest $request): JsonResponse
    {
        $property = Property::where('slug', $token)->firstOrFail();

        return $this->availability($request, $property);
    }
}
