<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Exceptions\Booking\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WidgetReservationStoreController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function __invoke(Request $request, string $propertyId): JsonResponse
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'customer' => ['required', 'array'],
            'customer.first_name' => ['required', 'string', 'min:2'],
            'customer.last_name' => ['required', 'string', 'min:2'],
            'customer.email' => ['required', 'email'],
            'customer.phone' => ['required', 'string'],
            'customer.note' => ['nullable', 'string'],
            'customer.is_company' => ['boolean'],
            'customer.company_name' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.ico' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.dic' => ['nullable', 'string'],
            'customer.has_vat' => ['boolean'],
            'customer.billing_street' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.billing_city' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.billing_zip' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.billing_country' => ['nullable', 'string'],
            'addons' => ['array'],
            'addons.*.service_id' => ['required', 'integer'],
            'addons.*.quantity' => ['required', 'integer', 'min:0'],
            'guests_count' => ['integer', 'min:1', 'max:50'],
        ]);

        try {
            // Validate that property exists (after tenancy is initialized)
            $property = Property::find($propertyId);
            if (!$property) {
                Log::warning('Property not found for booking', [
                    'property_id' => $propertyId,
                    'property_id_type' => gettype($propertyId),
                    'route_params' => request()->route()->parameters(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                ], 404);
            }

            // Prepare data for booking service
            $bookingData = array_merge($data, [
                'property_id' => $property->id, // Use the primary key (UUID)
                'notes' => $data['customer']['note'] ?? null,
                'guests_count' => $data['guests_count'] ?? 1,
            ]);

            Log::info('Booking data prepared', [
                'property_id' => $bookingData['property_id'],
                'property_id_from_model' => $property->id,
                'original_property_id' => $propertyId,
            ]);

            $booking = $this->bookingService->createBooking($bookingData);

            return response()->json([
                'success' => true,
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
                'total_price' => $booking->total_price_amount,
                'currency' => $booking->currency,
            ], 201);

        } catch (BookingException $e) {
            Log::warning('Booking creation failed', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
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
