<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Admin\Booking\BookingData;
use App\Data\Admin\Booking\BookingListData;
use App\Http\Controllers\Controller;
use App\Models\Booking\Booking;
use App\Models\CRM\Customer;
use App\Models\Property;
use App\States\Booking\BookingState;
use App\States\Booking\Cancelled;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\ModelStates\Validation\ValidStateRule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $currentPropertyId = $request->user()->current_property_id;

        $currentProperty = null;
        if ($currentPropertyId) {
            $currentProperty = Property::find($currentPropertyId);
        }

        $bookings = Booking::with(['property', 'guests'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Properties/Bookings/Index', [
            'property' => $currentProperty ? ['id' => $currentProperty->id, 'name' => $currentProperty->name] : null,
            'bookings' => BookingListData::collect($bookings),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['property', 'customer', 'payments', 'guests']);

        return Inertia::render('Admin/Properties/Bookings/Show', [
            'booking' => BookingData::from($booking)->toArray(),
        ]);
    }

    public function export(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $currentPropertyId = $request->user()->current_property_id;

        $bookings = Booking::with(['property', 'guests'])
            ->when($currentPropertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                return $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=bookings.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Property', 'Customer', 'Email', 'Phone', 'Check-in', 'Check-out', 'Status', 'Total Price', 'Notes']);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->property->name,
                    $booking->customer ? $booking->customer->name : 'N/A',
                    $booking->customer ? $booking->customer->email : 'N/A',
                    $booking->customer ? $booking->customer->phone : 'N/A',
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => ['required', ValidStateRule::make(BookingState::class)],
            'notes' => 'nullable|string',
        ]);

        $property = Property::findOrFail($validated['property_id']);

        if ($this->hasOverlap($property->id, $validated['check_in_date'], $validated['check_out_date'])) {
            return back()->withErrors(['check_in_date' => 'Selected dates are not available.']);
        }

        // Create a customer for the blocked date
        $customer = Customer::create([
            'name' => 'Blocked Date',
            'email' => 'blocked@system.local',
            'phone' => null,
        ]);

        $newBooking = Booking::create([
            'property_id' => $property->id,
            'customer_id' => $customer->id,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'total_price_amount' => 0,
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Dates blocked successfully.');
    }

    public function update(Request $request, Booking $booking)
    {
        // Load property if not already loaded
        if (! $booking->relationLoaded('property')) {
            $booking->load('property');
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'required', ValidStateRule::make(BookingState::class)],
            'check_in_date' => 'sometimes|required|date',
            'check_out_date' => 'sometimes|required|date|after:check_in_date',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['check_in_date']) || isset($validated['check_out_date'])) {
            $start = $validated['check_in_date'] ?? $booking->check_in_date;
            $end = $validated['check_out_date'] ?? $booking->check_out_date;

            if ($this->hasOverlap($booking->property_id, $start, $end, $booking->id)) {
                return back()->withErrors(['check_in_date' => 'Selected dates are not available.']);
            }
        }

        $booking->update($validated);

        return redirect()->back()->with('success', 'Booking updated.');
    }

    private function hasOverlap($propertyId, $startDate, $endDate, $ignoreBookingId = null)
    {
        return Booking::where('property_id', $propertyId)
            ->where('status', '!=', Cancelled::class)
            ->when($ignoreBookingId, function ($query, $ignoreBookingId) {
                return $query->where('id', '!=', $ignoreBookingId);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in_date', [$startDate, $endDate])
                    ->orWhereBetween('check_out_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('check_in_date', '<', $startDate)
                            ->where('check_out_date', '>', $endDate);
                    });
            })
            ->exists();
    }

    public function destroy(Booking $booking)
    {
        if (! $booking->relationLoaded('property')) {
            $booking->load('property');
        }

        $booking->delete();

        return redirect()->back()->with('success', 'Booking deleted.');
    }
}
