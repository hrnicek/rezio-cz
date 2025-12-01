<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Enums\BookingItemType;
use App\States\Booking\Cancelled;
use App\Http\Controllers\Controller;
use App\Http\Requests\Service\CheckAvailabilityRequest;
use App\Models\Booking\Booking;
use App\Models\Configuration\Service;
use App\Models\Property;
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
            ->get(['id', 'name', 'price_type', 'price_amount', 'max_quantity']);

        // Transform price_amount (cents) to price (float) for API if needed, 
        // or just return price_amount and let frontend handle it. 
        // The previous code returned 'price', let's see if we should map it.
        // Assuming frontend expects 'price' as float or integer. 
        // Let's return price_amount as 'price' for now to match existing structure roughly, 
        // but strictly it should be price_amount.
        // Old code: ->get(['id', 'name', 'price_type', 'price', 'max_quantity']);
        // New Service model likely has price_amount.

        $services = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price_type' => $service->price_type,
                'price' => $service->price_amount, // Return integer cents
                'max_quantity' => $service->max_quantity,
            ];
        });

        return response()->json([
            'services' => $services,
        ]);
    }

    public function availability(CheckAvailabilityRequest $request, Property $id): JsonResponse
    {
        $data = $request->validated();

        $timestamp = $id->updated_at?->timestamp ?? '0';
        $cacheKey = "widget_availability:{$id->id}:{$timestamp}:".md5(serialize($data));

        $result = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($data, $id) {
            $checkin = config('booking.checkin_time', '14:00');
            $checkout = config('booking.checkout_time', '10:00');
            $start = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'].' '.$checkin);
            $end = Carbon::createFromFormat('Y-m-d H:i', $data['end_date'].' '.$checkout);

            $overlappingBookings = Booking::query()
                ->where('property_id', $id->id)
                ->where('status', '!=', Cancelled::class)
                ->where('check_in_date', '<', $end)
                ->where('check_out_date', '>', $start)
                ->with(['items']) // Load items
                ->get();

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
                        'extra_id' => $serviceId,
                        'available_quantity' => 0,
                        'requested_quantity' => (int) $selection['quantity'],
                        'is_available' => false,
                    ];
                    $overallAvailable = false;
                    continue;
                }

                $bookedQty = 0;
                foreach ($overlappingBookings as $booking) {
                    // Filter items for this service name
                    // This assumes Service Name is unique and immutable for tracking
                    $items = $booking->items->filter(function ($item) use ($service) {
                        return $item->type === BookingItemType::Service && $item->name === $service->name;
                    });
                    
                    $bookedQty += $items->sum('quantity');
                }

                $availableQty = max(0, (int) $service->max_quantity - $bookedQty);
                $isAvailable = $availableQty >= (int) $selection['quantity'];
                $overallAvailable = $overallAvailable && $isAvailable;

                $resultItems[] = [
                    'service_id' => $service->id,
                    'extra_id' => $service->id,
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
