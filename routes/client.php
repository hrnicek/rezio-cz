<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Tenant\Client\BookingController;

Route::name('client.')
    ->group(function () {
        Route::middleware(['allow-iframe'])->group(function () {
            Route::get('/book/{token}', [BookingController::class, 'show'])->name('widget.show');
            Route::post('/book/{token}', [BookingController::class, 'store'])->name('widget.store');
        });
    });
