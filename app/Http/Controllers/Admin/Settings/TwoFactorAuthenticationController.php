<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class TwoFactorAuthenticationController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [new Middleware('password.confirm', only: ['show'])]
            : [];
    }

    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();

        $user = $request->user();
        $twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();

        $data = [
            'twoFactorEnabled' => $twoFactorEnabled,
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ];

        if ($twoFactorEnabled) {
            $data['qrCode'] = $user->twoFactorQrCodeSvg();
            $data['recoveryCodes'] = $user->recoveryCodes();
        }

        return Inertia::render('Admin/Settings/TwoFactor', $data);
    }
}
