<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Admin routes (from routes/admin.php)
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(base_path('routes/admin.php'));

    // Client routes (from routes/client.php)
    if (file_exists(base_path('routes/client.php'))) {
        require base_path('routes/client.php');
    }

    // Check-in routes (public, no auth required)
    Route::prefix('check-in/{token}')->name('check-in.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Guest\CheckInController::class, 'show'])->name('show');
        Route::post('/guests', [\App\Http\Controllers\Guest\CheckInController::class, 'store'])->name('guests.store');
        Route::put('/guests/{guest}', [\App\Http\Controllers\Guest\CheckInController::class, 'update'])->name('guests.update');
        Route::delete('/guests/{guest}', [\App\Http\Controllers\Guest\CheckInController::class, 'destroy'])->name('guests.destroy');
    });

    // Default tenant home page
    Route::get('/', function () {
        return redirect()->route('admin.bookings.index');
    })->middleware('auth');
});
