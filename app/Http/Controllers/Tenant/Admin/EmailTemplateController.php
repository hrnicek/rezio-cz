<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Admin\EmailTemplate\StoreEmailTemplateRequest;
use App\Http\Requests\Tenant\Admin\EmailTemplate\UpdateEmailTemplateRequest;
use App\Models\Communication\EmailTemplate;
use App\Models\Property;
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
                    'name' => 'Zrušení rezervace',
                    'description' => 'Úprava obsahu e-mailu odeslaného při stornování rezervace.',
                ],
                [
                    'type' => 'booking_rejected',
                    'name' => 'Zamítnutí rezervace',
                    'description' => 'Úprava obsahu e-mailu odeslaného při zamítnutí rezervace.',
                ],
                [
                    'type' => 'pre_arrival_info',
                    'name' => 'Informace před příjezdem',
                    'description' => 'Máme pro Vás důležité informace před příjezdem.',
                ],
            ],
        ]);
    }

    public function update(UpdateEmailTemplateRequest $request, Property $property, EmailTemplate $email_template)
    {
        $email_template->update($request->validated());

        return back();
    }

    public function store(StoreEmailTemplateRequest $request, Property $property)
    {
        $property->emailTemplates()->create($request->validated());

        return back();
    }
}
