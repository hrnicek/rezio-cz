<?php

namespace App\Http\Controllers\Tenant\Widgets\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CalendarRequest;
use App\Http\Requests\Booking\VerifyAvailabilityRequest;
use App\Http\Requests\Booking\VerifyCustomerRequest;
use App\Models\BlackoutDate;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Property;
use App\Services\SeasonalPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class WidgetController extends Controller
{
    public function index(Property $id, CalendarRequest $request): JsonResponse
    {
        $property = $id;
        $pricingService = new SeasonalPricingService();

        $month = (int) ($request->validated()['month'] ?? now()->month);
        $year = (int) ($request->validated()['year'] ?? now()->year);

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $bookings = Booking::query()
            ->where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where('date_start', '<', $periodEnd->copy()->endOfDay())
            ->where('date_end', '>', $periodStart->copy()->startOfDay())
            ->get();

        $blackouts = BlackoutDate::query()
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
            $isBlackout = $blackouts->contains(function (BlackoutDate $b) use ($date) {
                return $date->between($b->start_date, $b->end_date);
            });

            $isBooked = $bookings->contains(function (Booking $b) use ($date) {
                $bookingStart = \Illuminate\Support\Carbon::parse($b->date_start)->startOfDay();
                $bookingEndExclusive = \Illuminate\Support\Carbon::parse($b->date_end)->subDay()->startOfDay();
                return $date->between($bookingStart, $bookingEndExclusive);
            });

            $meetsLead = $date->gte($earliest);

            $price = $pricingService->getPriceForDate($property->id, $date);
            $season = $pricingService->getSeasonForDate($property->id, $date);

            $days[] = [
                'date' => $date->toDateString(),
                'available' => $meetsLead && ! ($isBlackout || $isBooked),
                'blackout' => $isBlackout,
                'price' => $price,
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

    public function verify(Property $id, VerifyAvailabilityRequest $request): JsonResponse
    {
        $property = $id;
        $data = $request->validated();

        $checkin = config('booking.checkin_time', '14:00');
        $checkout = config('booking.checkout_time', '10:00');
        $start = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'] . ' ' . $checkin);
        $end = Carbon::createFromFormat('Y-m-d H:i', $data['end_date'] . ' ' . $checkout);

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
            ->where('status', '!=', 'cancelled')
            ->where('date_start', '<', $end)
            ->where('date_end', '>', $start)
            ->get();
        $hasOverlap = $bookings->isNotEmpty();

        $blackouts = BlackoutDate::query()
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get();

        $unavailableDates = [];
        $cursor = $start->copy()->startOfDay();
        $cursorEnd = $end->copy()->startOfDay();

        while ($cursor->lte($cursorEnd)) {
            $isBlackout = $blackouts->contains(function (BlackoutDate $b) use ($cursor) {
                return $cursor->between($b->start_date, $b->end_date);
            });

            $isBooked = $bookings->contains(function (Booking $b) use ($cursor) {
                $bookingStart = \Illuminate\Support\Carbon::parse($b->date_start)->startOfDay();
                $bookingEndExclusive = \Illuminate\Support\Carbon::parse($b->date_end)->subDay()->startOfDay();
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
