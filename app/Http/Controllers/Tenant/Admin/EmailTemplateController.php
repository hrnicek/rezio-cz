<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index(Property $property)
    {
        return Inertia::render('Admin/Properties/EmailTemplates/Index', [
            'property' => $property,
            'templates' => $property->emailTemplates()->get(),
            'availableTypes' => [
                [
                    'type' => 'booking_confirmation',
                    'name' => 'Potvrzení rezervace',
                    'description' => 'Úprava obsahu automatického e-mailu, který host obdrží okamžitě po potvrzení rezervace.',
                ],
                [
                    'type' => 'payment_reminder',
                    'name' => 'Připomínka platby',
                    'description' => 'Úprava obsahu automatického e-mailu, který připomene hostu potřebu provedení platby za rezervaci.',
                ],
                [
                    'type' => 'payment_confirmation',
                    'name' => 'Potvrzení platby',
                    'description' => 'Úprava automatického e-mailu, který host obdrží po provedení platby za rezervaci.',
                ],
                [
                    'type' => 'payment_overdue',
                    'name' => 'Upomínka platby',
                    'description' => 'Úprava obsahu automatického e-mailu, který bude odeslán hostu při neuhrazené platbě za rezervaci.',
                ],
                [
                    'type' => 'booking_cancelled',
                    'name' => 'Vaše rezervace byla stornována',
                    'description' => 'Úprava obsahu e-mailu odeslaného při stornování rezervace.',
                ],
                [
                    'type' => 'booking_rejected',
                    'name' => 'Je nám líto, ale neuvidíme se',
                    'description' => 'Úprava obsahu e-mailu odeslaného při zamítnutí rezervace.',
                ],
                [
                    'type' => 'pre_arrival_info',
                    'name' => 'Už se těšíte?',
                    'description' => 'Máme pro Vás důležité informace před příjezdem.',
                ],
            ],
        ]);
    }

    public function update(Request $request, Property $property, EmailTemplate $email_template)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $email_template->update($validated);

        return back();
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $property->emailTemplates()->create($validated);

        return back();
    }
}
