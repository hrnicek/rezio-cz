<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Enums\ServicePriceType;
use App\Http\Controllers\Controller;
use App\Models\Configuration\Service;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class PropertyServiceController extends Controller
{
    public function index(Property $property)
    {
        return inertia('Admin/Properties/Services/Index', [
            'property' => $property,
            'services' => $property->services()
                ->orderBy('name')
                ->paginate(request('per_page', 10))
                ->withQueryString(),
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_type' => ['required', new Enum(ServicePriceType::class)],
            'price_amount' => 'required|numeric|min:0',
            'max_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Convert price from units (frontend) to cents (backend)
        $validated['price_amount'] = (int) round($validated['price_amount'] * 100);

        $property->services()->create($validated);

        return redirect()->route('admin.properties.services.index', $property);
    }

    public function update(Request $request, Property $property, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_type' => ['required', new Enum(ServicePriceType::class)],
            'price_amount' => 'required|numeric|min:0',
            'max_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Convert price from units (frontend) to cents (backend)
        $validated['price_amount'] = (int) round($validated['price_amount'] * 100);

        $service->update($validated);

        return redirect()->route('admin.properties.services.index', $property);
    }

    public function destroy(Property $property, Service $service)
    {
        $service->delete();

        return redirect()->route('admin.properties.services.index', $property);
    }
}
