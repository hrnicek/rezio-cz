<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use App\Http\Controllers\Tenant\Client\BookingController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'web',
])->group(function () {
Route::name('client.')
    ->group(function () {
        Route::middleware(['allow-iframe'])->group(function () {
            Route::get('app/widget/{token}', [BookingController::class, 'show'])->name('widget.show');
            Route::post('app/widget/{token}', [BookingController::class, 'store'])->name('widget.store');
        });
    });


    Route::prefix('app/check-in/{token}')->name('check-in.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'show'])->name('show');
        Route::post('/guests', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'store'])->name('guests.store');
        Route::put('/guests/{guest}', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'update'])->name('guests.update');
        Route::delete('/guests/{guest}', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'destroy'])->name('guests.destroy');
    });
});