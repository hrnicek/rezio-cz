<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Admin\BookingPayment\StoreBookingPaymentRequest;
use App\Models\Booking\Booking;
use App\Models\Finance\Payment;

class BookingPaymentController extends Controller
{
    public function store(StoreBookingPaymentRequest $request, Booking $booking)
    {
        $validated = $request->validated();

        // 1. Find or Create default Folio (Hlavní účet)
        $folio = $booking->folios()->first();
        /** @var \App\Models\Booking\Folio $folio */
        if (! $folio) {
            $folio = $booking->folios()->create([
                'customer_id' => $booking->customer_id,
                'name' => 'Hlavní účet',
                'status' => \App\States\Folio\Open::class,
                'currency' => $booking->currency ?? 'CZK',
                'total_amount' => 0,
            ]);
        }

        // Amount is coming in standard currency units (e.g. 1000 CZK), model expects cents (integer)
        $amountCents = (int) round($validated['amount'] * 100);

        $booking->payments()->create([
            'folio_id' => $folio->id,
            'amount' => $amountCents,
            'paid_at' => $validated['paid_at'],
            'currency' => $booking->currency ?? 'CZK',
            'status' => \App\States\Payment\Paid::class,
            'payment_method' => PaymentMethod::Manual,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Platba byla úspěšně přidána.');
    }

    public function destroy(Booking $booking, Payment $payment)
    {
        $payment->delete();

        return back()->with('success', 'Platba byla smazána.');
    }
}
