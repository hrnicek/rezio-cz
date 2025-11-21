<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $bookings = \App\Models\Booking::with('property')
            ->get()
            ->map(function ($booking) {
                return [
                    'key' => $booking->id,
                    'dates' => [
                        'start' => $booking->start_date,
                        'end' => $booking->end_date,
                    ],
                    'customData' => [
                        'title' => $booking->guest_info['name'],
                        'status' => $booking->status ?? '',
                        'notes' => $booking->notes,
                        'class' => match ($booking->status ?? '') {
                            'confirmed' => 'bg-green-500 text-white',
                            'pending' => 'bg-yellow-500 text-white',
                            'cancelled' => 'bg-red-500 text-white',
                            'blocked' => 'bg-gray-800 text-white',
                            default => 'bg-gray-500 text-white',
                        },
                    ],
                    'popover' => [
                        'label' => $booking->guest_info['name'] . ' (' . ($booking->status ?? '') . ')',
                    ],
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'bookings' => $bookings,
            'properties' => \App\Models\Property::get(['id', 'name']),
        ]);
    }
}
