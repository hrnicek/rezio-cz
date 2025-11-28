<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Data\Shared\PropertyData;
use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
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
            ...$validated,
            'slug' => Str::slug($validated['name']).'-'.Str::random(6),
        ]);

        if ($request->filled('image')) {
            $fileInfo = \RahulHaque\Filepond\Facades\Filepond::field($request->image)
                ->moveTo('properties/images/'.$property->id);
            $property->update(['image' => $fileInfo['filepath']]);
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

        $property->update($validated);

        if ($request->filled('image') && $request->image !== $property->image) {
            try {
                $fileInfo = \RahulHaque\Filepond\Facades\Filepond::field($request->image)
                    ->moveTo('properties/images/'.$property->id);
                $property->update(['image' => $fileInfo['filepath']]);
            } catch (\Throwable $e) {
                // Ignore if it's not a valid FilePond serverId (e.g. existing image path)
            }
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
