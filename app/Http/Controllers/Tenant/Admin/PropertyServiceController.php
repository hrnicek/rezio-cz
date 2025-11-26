<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Service;
use Illuminate\Http\Request;

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
            'price_type' => 'required|in:per_day,flat,per_stay',
            'price' => 'required|numeric|min:0',
            'max_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $property->services()->create($validated);

        return redirect()->route('admin.properties.services.index', $property);
    }

    public function update(Request $request, Property $property, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_type' => 'required|in:per_day,flat,per_stay',
            'price' => 'required|numeric|min:0',
            'max_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);

        return redirect()->route('admin.properties.services.index', $property);
    }

    public function destroy(Property $property, Service $service)
    {
        $service->delete();

        return redirect()->route('admin.properties.services.index', $property);
    }
}
