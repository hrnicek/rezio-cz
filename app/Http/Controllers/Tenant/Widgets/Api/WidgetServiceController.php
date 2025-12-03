<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Data\Shared\MoneyData;
use App\Enums\BookingItemType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Service\CheckAvailabilityRequest;
use App\Models\Booking\Booking;
use App\Models\Configuration\Service;
use App\Models\Property;
use App\States\Booking\Cancelled;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WidgetServiceController extends Controller
{
    public function index(string $propertyId): JsonResponse
    {
        $property = Property::query()->find($propertyId);
        if (! $property) {
            return response()->json(['error' => 'Property not found'], 404);
        }

        $services = Service::query()
            ->where('property_id', $property->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'price_type', 'price_amount', 'max_quantity']);

        $services = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price_type' => $service->price_type,
                'price' => MoneyData::fromModel($service->price_amount),
                'max_quantity' => $service->max_quantity,
            ];
        });

        return response()->json([
            'services' => $services,
        ]);
    }

    public function availability(CheckAvailabilityRequest $request, string $propertyId): JsonResponse
    {
        $property = Property::query()->find($propertyId);
        if (! $property) {
            return response()->json(['error' => 'Property not found'], 404);
        }

        $data = $request->validated();

        $timestamp = $property->updated_at?->timestamp ?? '0';
        $cacheKey = "widget_availability:{$property->id}:{$timestamp}:".md5(serialize($data));

        $result = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($data, $property) {
            $checkin = config('booking.checkin_time', '14:00');
            $checkout = config('booking.checkout_time', '10:00');
            $start = \Illuminate\Support\Facades\Date::createFromFormat('Y-m-d H:i', $data['start_date'].' '.$checkin);
            $end = \Illuminate\Support\Facades\Date::createFromFormat('Y-m-d H:i', $data['end_date'].' '.$checkout);

            $overlappingBookings = Booking::query()
                ->where('property_id', $property->uuid)
                ->where('status', '!=', Cancelled::class)
                ->where('check_in_date', '<', $end)
                ->where('check_out_date', '>', $start)
                ->with(['folios.items']) // Load items
                ->get();

            $serviceIds = collect($data['selections'])
                ->map(fn ($s) => $s['service_id'] ?? $s['extra_id'] ?? null)
                ->filter()
                ->unique();

            $services = Service::query()->whereIn('id', $serviceIds)->get()->keyBy('id');

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
                    /** @var \Illuminate\Database\Eloquent\Collection $folios */
                    $folios = $booking->folios;
                    $bookingItems = collect();
                    foreach ($folios as $folio) {
                        /** @var \App\Models\Booking\Folio $folio */
                        $bookingItems = $bookingItems->merge($folio->items);
                    }
                    $items = $bookingItems->filter(function (/** @var \App\Models\Booking\BookingItem $item */ $item) use ($service) {
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
        $property = Property::query()->where('slug', $token)->firstOrFail();

        return $this->availability($request, $property);
    }
}
