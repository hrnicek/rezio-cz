<?php

namespace App\Http\Controllers\Tenant\Widgets;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Inertia\Inertia;

class WidgetBookingController extends Controller
{
    public function show(Property $property)
    {
        $widget = $property->widget;

        return Inertia::render('Widget/Index', [
            'property' => $property,
            'widget' => $widget,
        ]);
    }
}
