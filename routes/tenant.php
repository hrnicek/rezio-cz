<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Api\AresController;
use App\Http\Controllers\Tenant\FilePondController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains;
use App\Http\Controllers\Tenant\Widgets\WidgetBookingController;
use App\Http\Controllers\Tenant\Admin\Auth\NewPasswordController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetServiceController;
use App\Http\Controllers\Tenant\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Tenant\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetReservationStoreController;

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
    InitializeTenancyBySubdomain::class,
    PreventAccessFromUnwantedDomains::class,
    'web',
])->group(function () {


    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login')
        ->middleware('guest');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store')
        ->middleware('guest');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout')
        ->middleware('auth');

    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request')
        ->middleware('guest');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware('guest');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset')
        ->middleware('guest');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store')
        ->middleware('guest');

    // Admin routes
    Route::name('admin.')->middleware(['auth'])->group(base_path('routes/admin.php'));
  
    Route::get('/ares/{ico}', [AresController::class, 'show'])
        ->name('api.ares.show');

    // FilePond Routes
    Route::prefix('filepond')->controller(FilePondController::class)->group(function () {
        Route::post('process', 'process')->name('filepond.process');
        Route::delete('revert', 'revert')->name('filepond.revert');
    });

    Route::get('widget/{property:id}', [WidgetBookingController::class, 'show'])->name('widget.show');

    Route::prefix('app/check-in/{token}')->name('check-in.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'show'])->name('show');
                Route::post('/guests', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'store'])->name('guests.store');
                Route::put('/guests/{guest}', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'update'])->name('guests.update');
                Route::delete('/guests/{guest}', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'destroy'])->name('guests.destroy');
            });

    Route::prefix('api/widgets/{id}')
        ->group(function () {
            Route::get('/', [WidgetController::class, 'index'])->name('api.widgets.index');
            Route::post('verify', [WidgetController::class, 'verify'])->name('api.widgets.verify');
            Route::post('verify-customer', [WidgetController::class, 'verifyCustomer'])->name('api.widgets.verify-customer');
            Route::post('/reservations', WidgetReservationStoreController::class)->name('api.widgets.reservations');

            Route::prefix('services')->group(function () {
                Route::get('/', [WidgetServiceController::class, 'index'])->name('api.widgets.services.index');
                Route::post('availability', [WidgetServiceController::class, 'availability'])->name('api.widgets.services.availability');
            });
        });
});
