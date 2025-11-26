<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Season;

use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index(Property $property)
    {
        return inertia('Admin/Properties/Seasons/Index', [
            'property' => $property,
            'seasons' => $property->seasons()->orderBy('start_date')->get(),
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'price' => 'required|numeric|min:0',
            'min_stay' => 'nullable|integer|min:1',
            'check_in_days' => 'nullable|array',
            'is_default' => 'nullable|boolean',
            'is_fixed_range' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'is_recurring' => 'nullable|boolean',
        ]);

        $season = $property->seasons()->create($validated);



        return redirect()->route('admin.properties.seasons.index', $property);
    }

    public function update(Request $request, Property $property, Season $season)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'price' => 'required|numeric|min:0',
            'min_stay' => 'nullable|integer|min:1',
            'check_in_days' => 'nullable|array',
            'is_default' => 'nullable|boolean',
            'is_fixed_range' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'is_recurring' => 'nullable|boolean',
        ]);

        $season->update($validated);



        return redirect()->route('admin.properties.seasons.index', $property);
    }

    public function destroy(Property $property, Season $season)
    {
        $season->delete();

        return redirect()->route('admin.properties.seasons.index', $property);
    }
}
