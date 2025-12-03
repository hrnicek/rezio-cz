<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Admin\PropertyService\StorePropertyServiceRequest;
use App\Http\Requests\Tenant\Admin\PropertyService\UpdatePropertyServiceRequest;
use App\Models\Configuration\Service;
use App\Models\Property;

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

    public function store(StorePropertyServiceRequest $request, Property $property)
    {
        $validated = $request->validated();

        // Convert price from units (frontend) to cents (backend)
        $validated['price_amount'] = (int) round($validated['price_amount'] * 100);

        $property->services()->create($validated);

        return to_route('admin.properties.services.index', $property);
    }

    public function update(UpdatePropertyServiceRequest $request, Property $property, Service $service)
    {
        $validated = $request->validated();

        // Convert price from units (frontend) to cents (backend)
        $validated['price_amount'] = (int) round($validated['price_amount'] * 100);

        $service->update($validated);

        return to_route('admin.properties.services.index', $property);
    }

    public function destroy(Property $property, Service $service)
    {
        $service->delete();

        return to_route('admin.properties.services.index', $property);
    }
}
