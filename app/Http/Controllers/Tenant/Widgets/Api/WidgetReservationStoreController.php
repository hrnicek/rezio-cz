<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Exceptions\Booking\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Widgets\Api\StoreWidgetReservationRequest;
use App\Http\Resources\Tenant\Widgets\Api\WidgetReservationResource;
use App\Models\Property;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WidgetReservationStoreController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function __invoke(StoreWidgetReservationRequest $request, string $propertyId): JsonResponse
    {
        $property = Property::query()->find($propertyId);

        if (! $property) {
            Log::warning('Property not found for booking', [
                'property_id' => $propertyId,
                'property_id_type' => gettype($propertyId),
                'route_params' => $request->route()->parameters(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Property not found',
            ], 404);
        }

        $data = $request->validated();

        $bookingData = array_merge($data, [
            'property_id' => $property->id,
            'notes' => $data['customer']['note'] ?? null,
            'guests_count' => $data['guests_count'] ?? 1,
        ]);

        Log::info('Booking data prepared', [
            'property_id' => $bookingData['property_id'],
            'property_id_from_model' => $property->id,
            'original_property_id' => $propertyId,
        ]);

        try {
            $booking = $this->bookingService->createBooking($bookingData);

            return response()->json(
                WidgetReservationResource::make($booking)->resolve(),
                201
            );
        } catch (BookingException $e) {
            Log::warning('Booking creation failed', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(), // Assuming getContext() exists based on original code
                'property_id' => $propertyId,
                'customer_email' => $data['customer']['email'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'type' => get_class($e),
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            Log::error('Unexpected error during booking creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'property_id' => $propertyId,
                'data' => $data,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }
}
