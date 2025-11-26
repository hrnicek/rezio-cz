<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\BookingPriceCalculator;
use App\Services\SeasonalPricingService;
use App\Models\BlackoutDate;

class WidgetReservationStoreController extends Controller
{
    public function __invoke(Request $request, Property $id): JsonResponse
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
            'addons' => ['array'],
            'addons.*.service_id' => ['required', 'integer'],
            'addons.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        $checkin = config('booking.checkin_time', '14:00');
        $checkout = config('booking.checkout_time', '10:00');
        $timezone = config('booking.timezone', 'Europe/Prague');

        $start = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'] . ' ' . $checkin, $timezone);
        $end = Carbon::createFromFormat('Y-m-d H:i', $data['end_date'] . ' ' . $checkout, $timezone);

        $minLeadDays = (int) config('booking.min_lead_days', 1);
        $earliest = now()->timezone($timezone)->startOfDay()->addDays($minLeadDays)->setTimeFromTimeString($checkin);
        if ($start->lt($earliest)) {
            return response()->json(['message' => 'Selected date is too soon.'], 422);
        }

        $overlappingBookings = Booking::query()
            ->where('property_id', $id->id)
            ->where('status', '!=', 'cancelled')
            ->where('date_start', '<', $end)
            ->where('date_end', '>', $start)
            ->get();

        $blackouts = BlackoutDate::query()
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get();

        if ($overlappingBookings->isNotEmpty() || $blackouts->isNotEmpty()) {
            return response()->json(['message' => 'Selected dates are unavailable.'], 422);
        }

        $customer = Customer::query()->firstOrCreate(
            ['email' => $data['customer']['email']],
            [
                'first_name' => $data['customer']['first_name'],
                'last_name' => $data['customer']['last_name'],
                'phone' => $data['customer']['phone'],
            ]
        );

        $priceCalculator = new BookingPriceCalculator(new SeasonalPricingService());
        $breakdown = $priceCalculator->calculate(
            $id->id,
            $start,
            $end,
            $data['addons'] ?? []
        );

        $booking = Booking::query()->create([
            'property_id' => $id->id,
            'customer_id' => $customer->id,
            'start_date' => $start->copy()->startOfDay(),
            'end_date' => $end->copy()->startOfDay(),
            'date_start' => $start,
            'date_end' => $end,
            'total_price' => $breakdown->total,
            'currency' => config('booking.currency', 'CZK'),
            'status' => 'pending',
            'notes' => $data['customer']['note'] ?? null,
        ]);

        foreach ($breakdown->serviceDetails as $svc) {
            $booking->services()->attach($svc['service_id'], [
                'quantity' => $svc['quantity'],
                'price_total' => $svc['line_total'],
            ]);
        }

        return response()->json([
            'success' => true,
            'booking_id' => $booking->id,
        ], 201);
    }
}
