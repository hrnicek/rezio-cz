<?php

namespace App\Http\Controllers\Tenant\Guest;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Guest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckInController extends Controller
{
    public function show(string $token)
    {
        $booking = Booking::where('checkin_token', $token)
            ->with(['property', 'guests'])
            ->firstOrFail();

        return Inertia::render('Guest/CheckIn/Show', [
            'booking' => $booking,
            'property' => $booking->property,
            'guests' => $booking->guests,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $booking = Booking::where('checkin_token', $token)->firstOrFail();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'is_adult' => 'required|boolean',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'document_type' => 'nullable|string',
            'document_number' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|array',
            'signature' => 'nullable|string',
        ]);

        $booking->guests()->create($validated);

        return redirect()->back()->with('success', 'Osoba byla úspěšně uložena.');
    }

    public function update(Request $request, string $token, Guest $guest)
    {
        $booking = Booking::where('checkin_token', $token)->firstOrFail();

        if ($guest->booking_id !== $booking->id) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'is_adult' => 'required|boolean',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'document_type' => 'nullable|string',
            'document_number' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|array',
            'signature' => 'nullable|string',
        ]);

        $guest->update($validated);

        return redirect()->back()->with('success', 'Údaje byly aktualizovány.');
    }

    public function destroy(string $token, Guest $guest)
    {
        $booking = Booking::where('checkin_token', $token)->firstOrFail();

        if ($guest->booking_id !== $booking->id) {
            abort(403);
        }

        $guest->delete();

        return redirect()->back()->with('success', 'Osoba byla smazána.');
    }
}
