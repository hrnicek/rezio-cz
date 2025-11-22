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
        $currentPropertyId = $request->user()->current_property_id;

        $bookings = Booking::with(['property', 'customer'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Properties/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['property', 'customer', 'payments']);

        return Inertia::render('Admin/Properties/Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'code' => $booking->code,
                'property' => [
                    'id' => $booking->property->id,
                    'name' => $booking->property->name,
                    'address' => $booking->property->address,
                ],
                'customer' => $booking->customer ? [
                    'id' => $booking->customer->id,
                    'first_name' => $booking->customer->first_name,
                    'last_name' => $booking->customer->last_name,
                    'email' => $booking->customer->email,
                    'phone' => $booking->customer->phone,
                    'note' => $booking->customer->note,
                ] : null,
                'start_date' => $booking->start_date->toDateString(),
                'end_date' => $booking->end_date->toDateString(),
                'total_price' => $booking->total_price,
                'status' => $booking->status,
                'notes' => $booking->notes,
                'created_at' => $booking->created_at->toISOString(),
                'updated_at' => $booking->updated_at->toISOString(),
                'payments' => $booking->payments->map(fn($payment) => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'paid_at' => $payment->paid_at->toISOString(),
                    'status' => $payment->status,
                ]),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $currentPropertyId = $request->user()->current_property_id;

        $bookings = Booking::with(['property', 'customer'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
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
                    $booking->customer ? $booking->customer->first_name . ' ' . $booking->customer->last_name : '',
                    $booking->customer->email ?? '',
                    $booking->customer->phone ?? '',
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

        // Create a customer for the blocked date
        $customer = \App\Models\Customer::create([
            'first_name' => 'Blocked',
            'last_name' => 'Date',
            'email' => 'blocked@system.local',
            'phone' => 'N/A',
        ]);

        $newBooking = Booking::create([
            'property_id' => $property->id,
            'user_id' => auth()->id(),
            'customer_id' => $customer->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
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
