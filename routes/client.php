<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\BookingController;

Route::name('client.')->group(function () {
    Route::get('/book/{token}', [BookingController::class, 'show'])->name('widget.show');
    Route::post('/book/{token}', [BookingController::class, 'store'])->name('widget.store');
});
