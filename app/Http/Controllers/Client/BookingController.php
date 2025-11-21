<?php

namespace App\Http\Controllers\Client;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function show(string $token)
    {
        $property = Property::where('widget_token', $token)->firstOrFail();

        return Inertia::render('Client/Booking/Index', [
            'property' => $property,
            'bookingConfig' => [
                'minLeadDays' => (int) config('booking.min_lead_days', 1),
                'timezone' => (string) config('booking.timezone', 'Europe/Prague'),
            ],
        ]);
    }

    public function store(Request $request, string $token)
    {
        $property = Property::where('widget_token', $token)->firstOrFail();

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
        ]);

        // Check for overlaps
        $overlap = Booking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($query) use ($validated) {
                        $query->where('start_date', '<', $validated['start_date'])
                            ->where('end_date', '>', $validated['end_date']);
                    });
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['start_date' => 'Selected dates are not available.']);
        }

        // Calculate price - now using property price_per_night directly
        // Seasonal pricing should be handled via Season model, not property-specific seasonal prices
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        $nights = $start->diffInDays($end);
        $totalPrice = $nights * $property->price_per_night;

        // NOTE: This controller is deprecated - bookings should be created via API routes
        // using CreateBookingAction which handles customer creation properly

        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $property->user_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Mail::to($validated['guest_email'])->queue(new \App\Mail\BookingConfirmation($booking));
        \Illuminate\Support\Facades\Mail::to($property->user->email)->queue(new \App\Mail\NewBookingAlert($booking));

        return redirect()->back()->with('success', 'Booking request sent successfully!');
    }
}
