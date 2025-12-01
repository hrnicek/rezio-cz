<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function edit(Property $property)
    {
        $property->load('billingSetting');

        // Ensure billing setting exists or use default
        $billingSetting = $property->billingSetting ?? $property->billingSetting()->make();

        return Inertia::render('Admin/Properties/Billing/Index', [
            'property' => $property,
            'billingSetting' => $billingSetting,
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'is_vat_payer' => 'boolean',
            'ico' => 'nullable|string|max:255',
            'dic' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'default_note' => 'nullable|string',

            'bank_account' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'swift' => 'nullable|string|max:255',
            'currency' => 'required|string|size:3',
            'show_bank_account' => 'boolean',

            'proforma_prefix' => 'nullable|string|max:20',
            'proforma_current_sequence' => 'nullable|integer',
            'invoice_prefix' => 'nullable|string|max:20',
            'invoice_current_sequence' => 'nullable|integer',
            'receipt_prefix' => 'nullable|string|max:20',
            'receipt_current_sequence' => 'nullable|integer',

            'due_days' => 'nullable|integer',
        ]);

        $property->billingSetting()->updateOrCreate(
            ['property_id' => $property->id],
            $validated
        );

        return Redirect::route('admin.properties.billing.edit', $property->id)
            ->with('success', 'Nastavení fakturace bylo uloženo.');
    }
}
