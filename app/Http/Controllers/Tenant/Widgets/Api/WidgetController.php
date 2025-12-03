<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Data\Shared\MoneyData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CalendarRequest;
use App\Http\Requests\Booking\VerifyAvailabilityRequest;
use App\Http\Requests\Booking\VerifyCustomerRequest;
use App\Models\Booking\Booking;
use App\Models\Configuration\BlockDate;
use App\Models\CRM\Customer;
use App\Models\Property;
use App\Services\SeasonalPricingService;
use App\States\Booking\Cancelled;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class WidgetController extends Controller
{
    public function index(string $propertyId, CalendarRequest $request): JsonResponse
    {
        $property = Property::query()->find($propertyId);
        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }
        $pricingService = new SeasonalPricingService;

        $month = (int) ($request->validated()['month'] ?? now()->month);
        $year = (int) ($request->validated()['year'] ?? now()->year);

        $periodStart = \Illuminate\Support\Facades\Date::create($year, $month, 1)->startOfMonth();
        $periodEnd = \Illuminate\Support\Facades\Date::create($year, $month, 1)->endOfMonth();

        $bookings = Booking::query()
            ->where('property_id', $property->id)
            ->where('status', '!=', Cancelled::class)
            ->where('check_in_date', '<', $periodEnd->copy()->endOfDay())
            ->where('check_out_date', '>', $periodStart->copy()->startOfDay())
            ->get();

        $blackouts = BlockDate::query()
            ->where('start_date', '<=', $periodEnd->toDateString())
            ->where('end_date', '>=', $periodStart->toDateString())
            ->get();

        $days = [];
        $date = $periodStart->copy();

        $minLeadDays = (int) config('booking.min_lead_days', 1);
        $earliest = now()->timezone(config('booking.timezone', 'Europe/Prague'))
            ->startOfDay()
            ->addDays($minLeadDays);

        while ($date->lte($periodEnd)) {
            $isBlackout = $blackouts->contains(function (BlockDate $b) use ($date) {
                return $date->between($b->start_date, $b->end_date);
            });

            $isBooked = $bookings->contains(function (Booking $b) use ($date) {
                $bookingStart = $b->check_in_date->startOfDay();
                $bookingEndExclusive = $b->check_out_date->copy()->subDay()->startOfDay();

                return $date->between($bookingStart, $bookingEndExclusive);
            });

            $meetsLead = $date->gte($earliest);

            $price = $pricingService->getPriceForDate($property->id, $date);
            $season = $pricingService->getSeasonForDate($property->id, $date);

            $days[] = [
                'date' => $date->toDateString(),
                'available' => $meetsLead && ! ($isBlackout || $isBooked),
                'blackout' => $isBlackout,
                'price' => MoneyData::fromModel($price, 'CZK'),
                'season' => $season ? [
                    'id' => $season->id,
                    'name' => $season->name,
                ] : null,
            ];

            $date = $date->addDay();
        }

        return response()->json([
            'month' => $month,
            'year' => $year,
            'days' => $days,
        ]);
    }

    public function verify(string $propertyId, VerifyAvailabilityRequest $request): JsonResponse
    {
        $property = Property::query()->find($propertyId);
        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }
        $data = $request->validated();

        $checkin = config('booking.checkin_time', '14:00');
        $checkout = config('booking.checkout_time', '10:00');
        $start = \Illuminate\Support\Facades\Date::createFromFormat('Y-m-d H:i', $data['start_date'].' '.$checkin);
        $end = \Illuminate\Support\Facades\Date::createFromFormat('Y-m-d H:i', $data['end_date'].' '.$checkout);

        $minLeadDays = (int) config('booking.min_lead_days', 1);
        $earliest = now()->timezone(config('booking.timezone', 'Europe/Prague'))
            ->startOfDay()
            ->addDays($minLeadDays)
            ->setTimeFromTimeString($checkin);
        if ($start->lt($earliest)) {
            return response()->json([
                'available' => false,
                'unavailable_dates' => [],
            ]);
        }

        $bookings = Booking::query()
            ->where('property_id', $property->id)
            ->where('status', '!=', Cancelled::class)
            ->where('check_in_date', '<', $end)
            ->where('check_out_date', '>', $start)
            ->get();
        $hasOverlap = $bookings->isNotEmpty();

        $blackouts = BlockDate::query()
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get();

        $unavailableDates = [];
        $cursor = $start->copy()->startOfDay();
        $cursorEnd = $end->copy()->startOfDay();

        while ($cursor->lte($cursorEnd)) {
            $isBlackout = $blackouts->contains(function (BlockDate $b) use ($cursor) {
                return $cursor->between($b->start_date, $b->end_date);
            });

            $isBooked = $bookings->contains(function (Booking $b) use ($cursor) {
                $bookingStart = $b->check_in_date->startOfDay();
                $bookingEndExclusive = $b->check_out_date->copy()->subDay()->startOfDay();

                return $cursor->between($bookingStart, $bookingEndExclusive);
            });

            if ($isBlackout || $isBooked) {
                $unavailableDates[] = $cursor->toDateString();
            }

            $cursor = $cursor->addDay();
        }

        $available = (! $hasOverlap) && count($unavailableDates) === 0;

        return response()->json([
            'available' => $available,
            'unavailable_dates' => $unavailableDates,
        ]);
    }

    public function verifyCustomer(VerifyCustomerRequest $request): JsonResponse
    {
        $data = $request->validated();

        $exists = false;
        $existingId = null;

        if (isset($data['customer']['email'])) {
            $existing = Customer::query()->where('email', $data['customer']['email'])->first();
            if ($existing) {
                $exists = true;
                $existingId = $existing->id;
            }
        }

        return response()->json([
            'valid' => true,
            'exists' => $exists,
            'customer_id' => $existingId,
        ]);
    }
}
