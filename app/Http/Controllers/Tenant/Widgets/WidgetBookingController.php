<?php

namespace App\Http\Controllers\Tenant\Widgets;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\Controller;

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
