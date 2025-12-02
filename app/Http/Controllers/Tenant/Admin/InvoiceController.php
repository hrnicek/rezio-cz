<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Shared\MoneyData;
use App\Http\Controllers\Controller;
use App\Models\Finance\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::query()
            ->with(['booking.property'])
            ->latest('issued_date')
            ->paginate(15)
            ->withQueryString();

        $invoices->getCollection()->transform(function ($invoice) {
            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'type' => $invoice->type,
                'type_label' => $invoice->type?->label(),
                'issued_date' => $invoice->issued_date?->format('d.m.Y'),
                'due_date' => $invoice->due_date?->format('d.m.Y'),
                'tax_date' => $invoice->tax_date?->format('d.m.Y'),
                'customer_name' => $invoice->customer_name,
                'property_name' => $invoice->booking?->property?->name ?? '-',
                'variable_symbol' => $invoice->variable_symbol,
                'total_price' => MoneyData::fromModel($invoice->total_price_amount, $invoice->currency),
                'status' => $invoice->status,
                'status_label' => $invoice->status?->label(),
                'status_color' => $invoice->status?->color(),
            ];
        });

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }
}
