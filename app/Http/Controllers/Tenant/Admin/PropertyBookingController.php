<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Admin\Booking\BookingListData;
use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyBookingController extends Controller
{
    public function index(Request $request, Property $property)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $bookings = $property->bookings()
            ->with(['customer', 'property'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->whereHas('customer', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    })->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Properties/Bookings/Index', [
            'property' => $property,
            'bookings' => BookingListData::collect($bookings),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function export(Request $request, Property $property)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $bookings = $property->bookings()
            ->with(['customer'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->whereHas('customer', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    })->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=bookings-'.$property->slug.'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($bookings, $property) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Property', 'Guest Name', 'Email', 'Phone', 'Check-in', 'Check-out', 'Status', 'Total Price', 'Notes']);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $property->name,
                    $booking->customer ? $booking->customer->first_name.' '.$booking->customer->last_name : '',
                    $booking->customer->email ?? '',
                    $booking->customer->phone ?? '',
                    $booking->check_in_date->format('Y-m-d'),
                    $booking->check_out_date->format('Y-m-d'),
                    $booking->status->label(),
                    $booking->total_price_amount->format(),
                    $booking->notes,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
