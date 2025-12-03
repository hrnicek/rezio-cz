<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Admin\Booking\CalendarBookingData;
use App\Http\Controllers\Controller;
use App\Models\Booking\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $currentPropertyId = $request->user()->current_property_id;

        $bookingsQuery = Booking::with(['property', 'customer'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            });

        // Fetch bookings for a wider range, e.g., +/- 3 months from now to cover likely views
        // or just all active bookings if the volume isn't huge.
        // For now, let's get +/- 6 months to be safe for navigation.
        $bookings = $bookingsQuery
            ->whereBetween('check_in_date', [now()->subMonths(6), now()->addMonths(6)])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(fn ($booking) => CalendarBookingData::fromModel($booking)->toArray());

        return Inertia::render('Admin/Calendar/Index', [
            'bookings' => $bookings,
            'properties' => \App\Models\Property::query()->select(['id', 'name'])->get(),
        ]);
    }
}
