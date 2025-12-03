<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Admin\Property\SeasonData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Admin\Season\StoreSeasonRequest;
use App\Http\Requests\Tenant\Admin\Season\UpdateSeasonRequest;
use App\Models\Configuration\Season;
use App\Models\Property;

class SeasonController extends Controller
{
    public function index(Property $property)
    {
        return inertia('Admin/Properties/Seasons/Index', [
            'property' => $property,
            'seasons' => SeasonData::collect(
                $property->seasons()
                    ->oldest('start_date')
                    ->paginate(request('per_page', 10))
                    ->withQueryString()
            ),
        ]);
    }

    public function store(StoreSeasonRequest $request, Property $property)
    {
        $validated = $request->validated();

        if ($request->boolean('is_default')) {
            $property->seasons()->where('is_default', true)->update(['is_default' => false]);
        }

        // Convert price (units) to price_amount (cents)
        $validated['price_amount'] = (int) ($validated['price'] * 100);
        unset($validated['price']);

        $property->seasons()->create($validated);

        return to_route('admin.properties.seasons.index', $property);
    }

    public function update(UpdateSeasonRequest $request, Property $property, Season $season)
    {
        $validated = $request->validated();

        if ($request->boolean('is_default') && ! $season->is_default) {
            $property->seasons()->where('is_default', true)->update(['is_default' => false]);
        }

        // Convert price (units) to price_amount (cents)
        $validated['price_amount'] = (int) ($validated['price'] * 100);
        unset($validated['price']);

        $season->update($validated);

        return to_route('admin.properties.seasons.index', $property);
    }

    public function destroy(Property $property, Season $season)
    {
        $season->delete();

        return to_route('admin.properties.seasons.index', $property);
    }
}
