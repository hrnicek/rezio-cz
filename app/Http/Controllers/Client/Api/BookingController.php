<?php

namespace App\Http\Controllers\Client\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CalendarRequest;
use App\Http\Requests\Booking\VerifyAvailabilityRequest;
use App\Http\Requests\Booking\VerifyCustomerRequest;
use App\Models\BlackoutDate;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function calendar(CalendarRequest $request): JsonResponse
    {
        $month = (int) ($request->validated()['month'] ?? now()->month);
        $year = (int) ($request->validated()['year'] ?? now()->year);

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $seasons = Season::query()->get();
        $defaultSeason = $seasons->firstWhere('is_default', true);

        $bookings = Booking::query()
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
            $customSeason = $seasons->first(function (Season $s) use ($date) {
                $md = $date->format('m-d');
                $startMd = $s->start_date->format('m-d');
                $endMd = $s->end_date->format('m-d');
                if ($startMd <= $endMd) {
                    return ! $s->is_default && $md >= $startMd && $md <= $endMd;
                }

                return ! $s->is_default && ($md >= $startMd || $md <= $endMd);
            });
            $season = $customSeason ?: $defaultSeason;

            $isBlackout = $blackouts->contains(function (BlackoutDate $b) use ($date) {
                return $date->between($b->start_date, $b->end_date);
            });

            $isBooked = $bookings->contains(function (Booking $b) use ($date) {
                $bookingStart = \Illuminate\Support\Carbon::parse($b->date_start)->startOfDay();
                $bookingEndExclusive = \Illuminate\Support\Carbon::parse($b->date_end)->subDay()->startOfDay();
                return $date->between($bookingStart, $bookingEndExclusive);
            });

            $meetsLead = $date->gte($earliest);

            $days[] = [
                'date' => $date->toDateString(),
                'available' => $meetsLead && ! ($isBlackout || $isBooked),
                'blackout' => $isBlackout,
                'season' => $season?->name,
                'season_is_default' => (bool) ($season?->is_default ?? false),
                'price' => $season?->price,
            ];

            $date = $date->addDay();
        }

        return response()->json([
            'month' => $month,
            'year' => $year,
            'days' => $days,
        ]);
    }

    public function verify(VerifyAvailabilityRequest $request): JsonResponse
    {
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

    public function calendarByProperty(string $token, CalendarRequest $request): JsonResponse
    {
        // For now, return calendar for all properties regardless of token
        // In the future, you could filter by specific property if needed
        return $this->calendar($request);
    }

    public function verifyByProperty(string $token, VerifyAvailabilityRequest $request): JsonResponse
    {
        // For now, verify availability for all properties regardless of token
        // In the future, you could filter by specific property if needed
        return $this->verify($request);
    }


    public function store(
        \Illuminate\Http\Request $request,
        \App\Actions\Booking\CreateBookingAction $createBookingAction
    ): JsonResponse {
        // Validate request
        $data = $request->validate([
            'property_id' => 'required|integer|exists:properties,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
            'customer.email' => 'required|email',
            'customer.first_name' => 'required|string',
            'customer.last_name' => 'required|string',
            'customer.phone' => 'required|string',
            'customer.note' => 'nullable|string',
            'addons' => 'nullable|array',
            'addons.*.service_id' => 'required_without:addons.*.extra_id|integer',
            'addons.*.extra_id' => 'nullable|integer', // Backward compatibility
            'addons.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $booking = $createBookingAction->execute($data);
            return response()->json($booking, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function storeByProperty(
        string $token,
        \Illuminate\Http\Request $request,
        \App\Actions\Booking\CreateBookingAction $createBookingAction
    ): JsonResponse {
        // Validate request
        $data = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
            'customer.email' => 'required|email',
            'customer.first_name' => 'required|string',
            'customer.last_name' => 'required|string',
            'customer.phone' => 'required|string',
            'addons' => 'nullable|array',
            'addons.*.service_id' => 'required_without:addons.*.extra_id|integer',
            'addons.*.extra_id' => 'nullable|integer', // Backward compatibility
            'addons.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $booking = $createBookingAction->execute($data);
            return response()->json($booking, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
