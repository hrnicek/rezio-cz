<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Admin\Property\PropertyData;
use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $properties = Property::latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Properties/Index', [
            'properties' => PropertyData::collect($properties),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Properties/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $property = Property::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'slug' => Str::slug($validated['name']).'-'.Str::random(6),
        ]);

        if ($request->filled('image')) {
            $tempPath = $request->image;
            if (Storage::disk('public')->exists($tempPath)) {
                $newPath = 'properties/images/'.$property->id.'/'.basename($tempPath);
                if (Storage::disk('public')->move($tempPath, $newPath)) {
                    $property->update(['image' => $newPath]);
                }
            }
        }

        return redirect()->route('admin.properties.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        return Inertia::render('Admin/Properties/Edit', [
            'property' => PropertyData::from($property->load('seasons')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $property->update([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->filled('image') && $request->image !== $property->image) {
            $tempPath = $request->image;

            // Only attempt to move if it looks like a temp path (or we just check existence)
            if (Storage::disk('public')->exists($tempPath)) {
                $newPath = 'properties/images/'.$property->id.'/'.basename($tempPath);

                // Clean up old image if it exists
                if ($property->image && Storage::disk('public')->exists($property->image)) {
                    Storage::disk('public')->delete($property->image);
                }

                if (Storage::disk('public')->move($tempPath, $newPath)) {
                    $property->update(['image' => $newPath]);
                }
            }
        } elseif (empty($request->image) && $property->image) {
            // If image is cleared
            if (Storage::disk('public')->exists($property->image)) {
                Storage::disk('public')->delete($property->image);
            }
            $property->update(['image' => null]);
        }

        return redirect()->route('admin.properties.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('admin.properties.index');
    }
}
