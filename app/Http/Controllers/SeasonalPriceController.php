<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SeasonalPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(\App\Models\Property $property)
    {
        return \Inertia\Inertia::render('SeasonalPrices/Index', [
            'property' => $property,
            'seasonalPrices' => $property->seasonalPrices()->orderBy('start_date')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, \App\Models\Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price_per_night' => 'required|numeric|min:0',
        ]);

        if ($this->hasOverlap($property, $validated['start_date'], $validated['end_date'])) {
            return back()->withErrors(['start_date' => 'This date range overlaps with an existing seasonal price.']);
        }

        $property->seasonalPrices()->create($validated);

        return back()->with('success', 'Seasonal price created.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Property $property, \App\Models\SeasonalPrice $seasonalPrice)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price_per_night' => 'required|numeric|min:0',
        ]);

        if ($this->hasOverlap($property, $validated['start_date'], $validated['end_date'], $seasonalPrice->id)) {
            return back()->withErrors(['start_date' => 'This date range overlaps with an existing seasonal price.']);
        }

        $seasonalPrice->update($validated);

        return back()->with('success', 'Seasonal price updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Property $property, \App\Models\SeasonalPrice $seasonalPrice)
    {
        $seasonalPrice->delete();

        return back()->with('success', 'Seasonal price deleted.');
    }

    private function hasOverlap(\App\Models\Property $property, $startDate, $endDate, $excludeId = null)
    {
        return $property->seasonalPrices()
            ->when($excludeId, function ($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }
}
