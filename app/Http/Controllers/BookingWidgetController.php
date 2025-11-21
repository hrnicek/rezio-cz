<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingWidgetController extends Controller
{
    public function show(string $token)
    {
        $property = Property::where('widget_token', $token)->firstOrFail();

        return Inertia::render('Widget/Show', [
            'property' => $property,
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

        // Calculate price
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);

        $totalPrice = $this->calculateTotalPrice($property, $start, $end);

        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => $property->user_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'guest_info' => [
                'name' => $validated['guest_name'],
                'email' => $validated['guest_email'],
                'phone' => $validated['guest_phone'],
            ],
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // Send emails
        \Illuminate\Support\Facades\Mail::to($validated['guest_email'])->send(new \App\Mail\BookingConfirmation($booking));
        \Illuminate\Support\Facades\Mail::to($property->user->email)->send(new \App\Mail\NewBookingAlert($booking));

        return redirect()->back()->with('success', 'Booking request sent successfully!');
    }

    private function calculateTotalPrice(Property $property, \Carbon\Carbon $start, \Carbon\Carbon $end)
    {
        $seasonalPrices = $property->seasonalPrices()
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->get();

        $totalPrice = 0;
        $currentDate = $start->copy();

        while ($currentDate->lt($end)) {
            $price = $property->price_per_night;

            foreach ($seasonalPrices as $season) {
                if ($currentDate->between($season->start_date, $season->end_date)) {
                    $price = $season->price_per_night;
                    break;
                }
            }

            $totalPrice += $price;
            $currentDate->addDay();
        }

        return $totalPrice;
    }
}
