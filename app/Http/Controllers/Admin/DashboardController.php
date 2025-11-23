<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Data\Admin\CalendarBookingData;
use App\Data\Admin\UpcomingBookingData;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentPropertyId = $request->user()->current_property_id;

        $bookingsQuery = Booking::with(['property', 'customer'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            });

        $bookings = $bookingsQuery
            ->clone()
            ->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get()
            ->map(fn($booking) => CalendarBookingData::fromModel($booking)->toArray());

        $upcomingBookings = $bookingsQuery
            ->clone()
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(5)
            ->get()
            ->map(fn($booking) => UpcomingBookingData::fromModel($booking)->toArray());

        return Inertia::render('Admin/Dashboard', [
            'bookings' => $bookings,
            'upcomingBookings' => $upcomingBookings,
            'properties' => \App\Models\Property::get(['id', 'name']),
        ]);
    }
}
