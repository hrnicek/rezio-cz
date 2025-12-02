<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentDeleted;
use App\Models\Finance\Invoice;

class HandlePaymentDeleted
{
    public function handle(PaymentDeleted $event): void
    {
        $payment = $event->payment;

        // Find the invoice associated with this payment
        // We use paranoid check, though usually only one invoice per payment
        $invoice = Invoice::where('payment_id', $payment->id)->first();

        if ($invoice) {
            // Soft delete the invoice
            $invoice->delete();
        }
    }
}
