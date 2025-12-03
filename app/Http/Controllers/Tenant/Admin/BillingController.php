<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Admin\Billing\UpdateBillingSettingsRequest;
use App\Models\Property;
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

    public function update(UpdateBillingSettingsRequest $request, Property $property)
    {
        $property->billingSetting()->updateOrCreate(
            ['property_id' => $property->id],
            $request->validated()
        );

        return to_route('admin.properties.billing.edit', $property->id)
            ->with('success', 'Nastavení fakturace bylo uloženo.');
    }
}
