<?php

namespace App\Listeners\Payment;

use App\States\Invoice\Paid as InvoicePaid;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Events\Payment\PaymentCreated;
use App\Events\Payment\PaymentUpdated;
use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\States\Payment\Paid;

class CreatePaymentConfirmationInvoice
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        // Support both Created and Updated events
        if (! ($event instanceof PaymentCreated) && ! ($event instanceof PaymentUpdated)) {
            return;
        }

        $payment = $event->payment;
        /** @var \App\Models\Finance\Payment $payment */

        // For Updated event, check if status was changed to Paid
        if ($event instanceof PaymentUpdated) {
            // If it wasn't changed to Paid, or it was already Paid before, we might skip?
            // Ideally we only want to trigger this when transitioning TO Paid.
            // However, the event object doesn't easily carry 'wasChanged' state after model is refreshed or serialized.
            // But standard model events usually fire after save.
            // If we use explicit Dispatchable events from model, we might rely on the current state.

            if (! ($payment->status instanceof Paid)) {
                return;
            }

            // Check if invoice already exists to avoid duplicates
            if ($this->invoiceExists($payment)) {
                return;
            }
        }

        // For Created event
        if ($event instanceof PaymentCreated) {
            if (! ($payment->status instanceof Paid)) {
                return;
            }
        }

        $this->createInvoiceForPayment($payment);
    }

    protected function invoiceExists(Payment $payment): bool
    {
        // Check if a confirmation invoice already exists for this payment
        return Invoice::query()
            ->where('payment_id', $payment->id)
            ->where('type', InvoiceType::PaymentConfirmation)
            ->exists();
    }

    protected function createInvoiceForPayment(Payment $payment): void
    {
        $booking = $payment->booking;
        if (! $booking) {
            return;
        }
        /** @var \App\Models\Booking\Booking $booking */

        $customer = $booking->customer;

        $invoice = new Invoice;
        $invoice->booking_id = $payment->booking_id;
        $invoice->folio_id = $payment->folio_id;
        $invoice->payment_id = $payment->id;
        $invoice->type = InvoiceType::PaymentConfirmation;
        $invoice->status = new InvoicePaid($invoice);

        $invoice->number = $this->generateInvoiceNumber();
        $invoice->variable_symbol = $invoice->number;

        $invoice->issued_date = now();
        $invoice->tax_date = now();
        $invoice->due_date = now();

        // Supplier Data
        $property = $booking->property;
        $billing = $property->billingSetting;

        $invoice->supplier_name = $billing?->company_name ?? $property->name;
        $invoice->supplier_ico = $billing?->ico;
        $invoice->supplier_dic = $billing?->dic;

        $supplierAddressParts = array_filter([
            $billing?->street ?? $property->address,
            $billing?->city,
            $billing?->zip,
            $billing?->country,
        ]);
        $invoice->supplier_address = implode(', ', $supplierAddressParts) ?: 'Adresa neuvedena';

        // Customer Data
        if ($customer) {
            $invoice->customer_name = $customer->billing_name; // Use accessor
            $invoice->customer_ico = $customer->ico;
            $invoice->customer_dic = $customer->dic;

            $addressParts = array_filter([
                $customer->billing_street,
                $customer->billing_city,
                $customer->billing_zip,
                $customer->billing_country,
            ]);
            $invoice->customer_address = implode(', ', $addressParts) ?: 'Adresa neuvedena';
        } else {
            $invoice->customer_name = 'Host';
            $invoice->customer_address = 'Adresa neuvedena';
        }

        $invoice->total_price_amount = $payment->amount;
        $invoice->total_net_amount = $payment->amount; // 0% DPH pro zálohu/platbu
        $invoice->total_tax_amount = 0;
        $invoice->currency = $payment->currency;

        $invoice->save();

        // Create a line item for the payment
        $methodLabel = $payment->payment_method instanceof PaymentMethod
            ? $payment->payment_method->label()
            : $payment->payment_method;

        $invoice->items()->create([
            'name' => 'Přijatá platba'.($methodLabel ? " - {$methodLabel}" : ''),
            'quantity' => 1,
            'unit_price_amount' => $payment->amount,
            'total_price_amount' => $payment->amount,
            'tax_rate' => 0,
            'tax_amount' => 0,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = (string) $year;

        $lastInvoice = Invoice::query()
            ->where('number', 'like', "$prefix%")
            ->where('type', '!=', InvoiceType::Proforma)
            ->orderByDesc('number')
            ->first();

        if (! $lastInvoice) {
            return $prefix.'00001';
        }

        // Assuming number is numeric string like "202500001"
        $lastNumber = (int) substr($lastInvoice->number, 4);

        return $prefix.str_pad((string) ($lastNumber + 1), 5, '0', STR_PAD_LEFT);
    }
}
