<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Enums\BookingItemType;
use App\Http\Controllers\Controller;
use App\Models\Booking\Booking;
use App\Models\Configuration\BlockDate;
use App\Models\CRM\Customer;
use App\Models\Property;
use App\Services\BookingPriceCalculator;
use App\Services\SeasonalPricingService;
use App\States\Booking\Cancelled;
use App\States\Booking\Pending;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        ]);

        $checkin = config('booking.checkin_time', '14:00');
        $checkout = config('booking.checkout_time', '10:00');
        $timezone = config('booking.timezone', 'Europe/Prague');

        $start = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'].' '.$checkin, $timezone);
        $end = Carbon::createFromFormat('Y-m-d H:i', $data['end_date'].' '.$checkout, $timezone);

        $minLeadDays = (int) config('booking.min_lead_days', 1);
        $earliest = now()->timezone($timezone)->startOfDay()->addDays($minLeadDays)->setTimeFromTimeString($checkin);
        if ($start->lt($earliest)) {
            return response()->json(['message' => 'Selected date is too soon.'], 422);
        }

        $overlappingBookings = Booking::query()
            ->where('property_id', $id->id)
            ->where('status', '!=', Cancelled::class)
            ->where('check_in_date', '<', $end)
            ->where('check_out_date', '>', $start)
            ->exists();

        $blackouts = BlockDate::query()
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->exists();

        if ($overlappingBookings || $blackouts) {
            return response()->json(['message' => 'Selected dates are unavailable.'], 422);
        }

        $priceCalculator = new BookingPriceCalculator(new SeasonalPricingService);
        $breakdown = $priceCalculator->calculate(
            $id->id,
            $start,
            $end,
            $data['addons'] ?? []
        );

        return DB::transaction(function () use ($id, $data, $start, $end, $breakdown) {
            $customer = Customer::query()->updateOrCreate(
                ['email' => $data['customer']['email']],
                [
                    'first_name' => $data['customer']['first_name'],
                    'last_name' => $data['customer']['last_name'],
                    'phone' => $data['customer']['phone'],
                    'is_company' => $data['customer']['is_company'] ?? false,
                    'company_name' => $data['customer']['company_name'] ?? null,
                    'ico' => $data['customer']['ico'] ?? null,
                    'dic' => $data['customer']['dic'] ?? null,
                    'has_vat' => $data['customer']['has_vat'] ?? false,
                    'billing_street' => $data['customer']['billing_street'] ?? null,
                    'billing_city' => $data['customer']['billing_city'] ?? null,
                    'billing_zip' => $data['customer']['billing_zip'] ?? null,
                    'billing_country' => $data['customer']['billing_country'] ?? 'CZ',
                ]
            );

            $booking = Booking::query()->create([
                'property_id' => $id->id,
                'customer_id' => $customer->id,
                'check_in_date' => $start,
                'check_out_date' => $end,
                'total_price_amount' => $breakdown->total,
                'currency' => config('booking.currency', 'CZK'),
                'status' => Pending::class,
                'notes' => $data['customer']['note'] ?? null,
                'code' => strtoupper(Str::random(8)), // Ensure code generation
            ]);

            // Create Default Folio
            $folio = $booking->folios()->create([
                'customer_id' => $customer->id,
                'name' => 'Hlavní účet',
                'status' => \App\States\Folio\Open::class,
                'total_amount' => $breakdown->total,
                'currency' => config('booking.currency', 'CZK'),
            ]);

            // Add Accommodation Item
            if ($breakdown->accommodation > 0) {
                $folio->items()->create([
                    'booking_id' => $booking->id,
                    'type' => BookingItemType::Night,
                    'name' => 'Ubytování',
                    'quantity' => 1,
                    'unit_price_amount' => $breakdown->accommodation,
                    'total_price_amount' => $breakdown->accommodation,
                ]);
            }

            // Add Service Items
            foreach ($breakdown->serviceDetails as $svc) {
                $folio->items()->create([
                    'booking_id' => $booking->id,
                    'type' => BookingItemType::Service,
                    'name' => $svc['name'],
                    'quantity' => $svc['quantity'],
                    'unit_price_amount' => $svc['price_per_unit'],
                    'total_price_amount' => $svc['line_total'],
                ]);
            }

            return response()->json([
                'success' => true,
                'booking_id' => $booking->id,
            ], 201);
        });
    }
}
