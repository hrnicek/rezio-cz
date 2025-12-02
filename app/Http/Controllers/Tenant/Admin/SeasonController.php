<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Admin\Property\SeasonData;
use App\Http\Controllers\Controller;
use App\Models\Configuration\Season;
use App\Models\Property;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index(Property $property)
    {
        return inertia('Admin/Properties/Seasons/Index', [
            'property' => $property,
            'seasons' => SeasonData::collect(
                $property->seasons()
                    ->orderBy('start_date')
                    ->paginate(request('per_page', 10))
                    ->withQueryString()
            ),
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'boolean',
            'start_date' => 'exclude_if:is_default,true|required|date',
            'end_date' => 'exclude_if:is_default,true|required|date|after:start_date',
            'price' => 'required|numeric|min:0',
            'min_stay' => 'nullable|integer|min:1',
            'check_in_days' => 'nullable|array',
            'is_fixed_range' => 'boolean',
            'priority' => 'nullable|integer',
            'is_recurring' => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            $property->seasons()->where('is_default', true)->update(['is_default' => false]);
        }

        // Convert price (units) to price_amount (cents)
        $validated['price_amount'] = (int) ($validated['price'] * 100);
        unset($validated['price']);

        $property->seasons()->create($validated);

        return redirect()->route('admin.properties.seasons.index', $property);
    }

    public function update(Request $request, Property $property, Season $season)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'boolean',
            'start_date' => 'exclude_if:is_default,true|required|date',
            'end_date' => 'exclude_if:is_default,true|required|date|after:start_date',
            'price' => 'required|numeric|min:0',
            'min_stay' => 'nullable|integer|min:1',
            'check_in_days' => 'nullable|array',
            'is_fixed_range' => 'boolean',
            'priority' => 'nullable|integer',
            'is_recurring' => 'boolean',
        ]);

        if ($request->boolean('is_default') && ! $season->is_default) {
            $property->seasons()->where('is_default', true)->update(['is_default' => false]);
        }

        // Convert price (units) to price_amount (cents)
        $validated['price_amount'] = (int) ($validated['price'] * 100);
        unset($validated['price']);

        $season->update($validated);

        return redirect()->route('admin.properties.seasons.index', $property);
    }

    public function destroy(Property $property, Season $season)
    {
        $season->delete();

        return redirect()->route('admin.properties.seasons.index', $property);
    }
}
