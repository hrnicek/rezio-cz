<?php

namespace App\Http\Controllers\Tenant\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Booking\Booking;
use App\Http\Controllers\Controller;
use App\Data\Admin\Booking\CalendarBookingData;
use App\Data\Admin\Booking\UpcomingBookingData;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentPropertyId = $request->user()->current_property_id;

        $bookingsQuery = Booking::with(['property', 'customer'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            });

        $bookingsCollection = $bookingsQuery
            ->clone()
            ->whereBetween('check_in_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();

        $stats = [
            'total_revenue' => $bookingsCollection->where('status', '!=', 'cancelled')->sum('total_price_amount'),
            'total_bookings' => $bookingsCollection->count(),
            'pending_bookings' => $bookingsCollection->where('status', 'pending')->count(),
        ];

        $bookings = $bookingsCollection
            ->map(fn ($booking) => CalendarBookingData::fromModel($booking)->toArray());

        $upcomingBookings = $bookingsQuery
            ->clone()
            ->where('check_in_date', '>=', now())
            ->orderBy('check_in_date')
            ->take(5)
            ->get()
            ->map(fn ($booking) => UpcomingBookingData::fromModel($booking)->toArray());

        return Inertia::render('Admin/Dashboard', [
            'bookings' => $bookings,
            'upcomingBookings' => $upcomingBookings,
            'properties' => \App\Models\Property::withCount([
                'bookings',
                'bookings as active_bookings_count' => function ($query) {
                    $query->where('check_out_date', '>=', now());
                },
                'bookings as month_bookings_count' => function ($query) {
                    $query->whereBetween('check_in_date', [now()->startOfMonth(), now()->endOfMonth()]);
                }
            ])->get(['id', 'name', 'address', 'description']),
            'stats' => $stats,
        ]);
    }
}
