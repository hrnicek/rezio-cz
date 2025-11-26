<?php

namespace App\Http\Controllers\Tenant\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    public function show(Request $request): Response
    {
        // Two-factor authentication is not implemented yet
        return Inertia::render('Admin/Settings/TwoFactor', [
            'twoFactorEnabled' => false,
            'requiresConfirmation' => false,
        ]);
    }
}
