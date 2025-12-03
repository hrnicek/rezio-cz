<?php

namespace App\Http\Controllers\Tenant\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Admin\Settings\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back();
    }
}
