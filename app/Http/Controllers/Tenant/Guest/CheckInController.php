<?php

namespace App\Http\Controllers\Tenant\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Guest\CheckIn\StoreGuestRequest;
use App\Http\Requests\Tenant\Guest\CheckIn\UpdateGuestRequest;
use App\Models\Booking\Booking;
use App\Models\CRM\Guest;
use Inertia\Inertia;

class CheckInController extends Controller
{
    public function show(string $code)
    {
        $booking = Booking::query()->where('code', $code)
            ->with(['property', 'guests'])
            ->firstOrFail();

        return Inertia::render('Guest/CheckIn/Show', [
            'booking' => $booking,
            'property' => $booking->property,
            'guests' => $booking->guests,
        ]);
    }

    public function store(StoreGuestRequest $request, string $code)
    {
        $booking = Booking::query()->where('code', $code)->firstOrFail();

        $booking->guests()->create($request->validated());

        return back()->with('success', 'Osoba byla úspěšně uložena.');
    }

    public function update(UpdateGuestRequest $request, string $token, Guest $guest)
    {
        $booking = Booking::query()->where('token', $token)->firstOrFail();

        if ($guest->booking_id !== $booking->id) {
            abort(403);
        }

        $guest->update($request->validated());

        return back()->with('success', 'Údaje byly aktualizovány.');
    }

    public function destroy(string $token, Guest $guest)
    {
        $booking = Booking::query()->where('token', $token)->firstOrFail();

        if ($guest->booking_id !== $booking->id) {
            abort(403);
        }

        $guest->delete();

        return back()->with('success', 'Osoba byla smazána.');
    }
}
