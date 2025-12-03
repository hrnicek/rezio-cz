<?php

namespace App\Listeners\Booking;

use App\Enums\InvoiceType;
use App\Events\Booking\BookingCreated;
use App\Models\Finance\Invoice;
use App\States\Invoice\Issued;

class CreateProformaInvoice
{
    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;

        // Prevent duplicates (naive check)
        if (Invoice::where('booking_id', $booking->id)->where('type', InvoiceType::Proforma)->exists()) {
            return;
        }

        // Ensure we have something to invoice
        $totalAmount = $booking->total_price_amount instanceof \App\Support\Money ? $booking->total_price_amount->getAmount() : $booking->total_price_amount;
        if ($totalAmount <= 0) {
            return;
        }

        $this->createProforma($booking);
    }

    protected function createProforma($booking): void
    {
        $customer = $booking->customer;
        $property = $booking->property;
        $billing = $property->billingSetting;

        $invoice = new Invoice;
        $invoice->booking_id = $booking->id;
        // Link to main folio if exists, or find first folio
        $invoice->folio_id = $booking->folios()->first()?->id;

        $invoice->type = InvoiceType::Proforma;
        $invoice->status = Issued::class; // Proforma is issued immediately

        $invoice->number = $this->generateInvoiceNumber();
        $invoice->variable_symbol = $invoice->number;

        $invoice->issued_date = now();
        $invoice->tax_date = now();
        $invoice->due_date = now()->addDays($billing?->due_days ?? 14); // Default 14 days

        // Supplier Data
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
            $invoice->customer_name = $customer->billing_name;
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

        $invoice->total_price_amount = $booking->total_price_amount;
        $invoice->total_net_amount = $booking->total_price_amount; // Proforma usually matches total, tax calc might differ based on VAT
        // For simplicity, we treat proforma as simple request for payment.
        // If VAT payer, we should calculate tax.
        // But Booking total_price_amount is usually gross.

        $invoice->currency = $booking->currency;
        $invoice->total_tax_amount = 0; // Calculate properly if needed, for now simple

        $invoice->save();

        // Add Item: "Ubytování {date} - {date}"
        $invoice->items()->create([
            'name' => "Rezervace ubytování {$booking->check_in_date->format('d.m.Y')} - {$booking->check_out_date->format('d.m.Y')}",
            'quantity' => 1,
            'unit_price_amount' => $booking->total_price_amount,
            'total_price_amount' => $booking->total_price_amount,
            'tax_rate' => 0,
            'tax_amount' => 0,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        // Different sequence for Proforma? usually yes.
        // Let's stick to year + sequence for now, maybe prefixed 'P'?
        // User didn't specify prefix logic, but usually Proforma has different series.
        // Let's use "202590001" (9xxxxx series for proforma) or just "P2025..."
        // For simplicity and integer-like sorting in DB (if number is string but numeric):
        // Let's try to find if BillingSetting has prefix.

        $year = now()->year;
        $prefix = (string) $year.'9'; // 20259...

        $lastInvoice = Invoice::query()
            ->where('type', InvoiceType::Proforma)
            ->where('number', 'like', "$prefix%")
            ->orderByDesc('number')
            ->first();

        if (! $lastInvoice) {
            return $prefix.'00001';
        }

        $lastNumber = (int) substr($lastInvoice->number, 5); // after 20259

        return $prefix.str_pad((string) ($lastNumber + 1), 5, '0', STR_PAD_LEFT);
    }
}
