<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentPropertyId = $request->user()->current_property_id;

        $bookings = Booking::with(['property', 'customer'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            })
            ->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'start' => $booking->start_date->format('Y-m-d'),
                    'end' => $booking->end_date->format('Y-m-d'),
                    'title' => $booking->customer ? $booking->customer->first_name . ' ' . $booking->customer->last_name : 'Unknown',
                    'status' => $booking->status ?? '',
                ];
            });

        $upcomingBookings = Booking::with(['property', 'customer'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            })
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'label' => ($booking->customer ? $booking->customer->first_name . ' ' . $booking->customer->last_name : 'Unknown') . ' (' . ($booking->status ?? '') . ')',
                    'start' => $booking->start_date->format('Y-m-d'),
                    'end' => $booking->end_date->format('Y-m-d'),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'bookings' => $bookings,
            'upcomingBookings' => $upcomingBookings, // Added upcomingBookings
            'properties' => \App\Models\Property::get(['id', 'name']),
        ]);
    }
}
