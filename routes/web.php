<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Application;

// Central domain routes (landing page, authentication, etc.)
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return Inertia::render('Welcome', [
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
                'laravelVersion' => Application::VERSION,
                'phpVersion' => PHP_VERSION,
            ]);
        });

        // Authentication routes are registered by Fortify
        // and will automatically be scoped to central domains
    });
}

// Check-in routes moved to tenant routes
// (will be accessible on tenant domains only)
