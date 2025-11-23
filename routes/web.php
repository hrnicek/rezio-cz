<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Application;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::prefix('check-in/{token}')->name('check-in.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Guest\CheckInController::class, 'show'])->name('show');
    Route::post('/guests', [\App\Http\Controllers\Guest\CheckInController::class, 'store'])->name('guests.store');
    Route::put('/guests/{guest}', [\App\Http\Controllers\Guest\CheckInController::class, 'update'])->name('guests.update');
    Route::delete('/guests/{guest}', [\App\Http\Controllers\Guest\CheckInController::class, 'destroy'])->name('guests.destroy');
});
