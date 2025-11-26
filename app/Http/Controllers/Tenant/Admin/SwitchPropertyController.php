<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchPropertyController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
        ]);

        $property = Property::findOrFail($validated['property_id']);

        if (! $request->user()->switchProperty($property)) {
            abort(403, 'You do not have access to this property.');
        }

        return redirect()->back();
    }
}
