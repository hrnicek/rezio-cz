<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Property;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $bookings = Booking::with(['property'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('guest_info->name', 'like', "%{$search}%")
                        ->orWhere('guest_info->email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $bookings = Booking::with(['property'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('guest_info->name', 'like', "%{$search}%")
                        ->orWhere('guest_info->email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=bookings.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Property', 'Guest Name', 'Email', 'Phone', 'Check-in', 'Check-out', 'Status', 'Total Price', 'Notes']);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->property->name,
                    $booking->guest_info['name'] ?? '',
                    $booking->guest_info['email'] ?? '',
                    $booking->guest_info['phone'] ?? '',
                    $booking->start_date->format('Y-m-d'),
                    $booking->end_date->format('Y-m-d'),
                    $booking->status ?? '',
                    $booking->total_price,
                    $booking->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => ['required', Rule::in(Booking::ALLOWED_STATUSES)], // Updated validation
            'notes' => 'nullable|string',
        ]);

        $property = \App\Models\Property::findOrFail($validated['property_id']);

        if ($this->hasOverlap($property->id, $validated['start_date'], $validated['end_date'])) {
            return back()->withErrors(['start_date' => 'Selected dates are not available.']);
        }

        $newBooking = Booking::create([
            'property_id' => $property->id,
            'user_id' => auth()->id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'guest_info' => ['name' => 'Blocked'],
            'total_price' => 0,
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Dates blocked successfully.');
    }

    public function update(Request $request, Booking $booking)
    {
        // Load property if not already loaded
        if (!$booking->relationLoaded('property')) {
            $booking->load('property');
        }

        // Ownership check removed (tenanting will be added later)

        $validated = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(Booking::ALLOWED_STATUSES)], // Updated validation
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['start_date']) || isset($validated['end_date'])) {
            $start = $validated['start_date'] ?? $booking->start_date;
            $end = $validated['end_date'] ?? $booking->end_date;

            if ($this->hasOverlap($booking->property_id, $start, $end, $booking->id)) {
                return back()->withErrors(['start_date' => 'Selected dates are not available.']);
            }
        }

        $booking->update($validated);

        return redirect()->back()->with('success', 'Booking updated.');
    }

    private function hasOverlap($propertyId, $startDate, $endDate, $ignoreBookingId = null)
    {
        return Booking::where('property_id', $propertyId)
            ->where('status', '!=', 'cancelled')
            ->when($ignoreBookingId, function ($query, $ignoreBookingId) {
                return $query->where('id', '!=', $ignoreBookingId);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                    });
            })
            ->exists();
    }

    public function destroy(Booking $booking)
    {
        // Load property if not already loaded
        if (!$booking->relationLoaded('property')) {
            $booking->load('property');
        }

        // Ownership check removed (tenanting will be added later)

        $booking->delete();

        return redirect()->back()->with('success', 'Booking deleted.');
    }
}
