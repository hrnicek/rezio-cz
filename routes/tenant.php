<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Api\AresController;
use App\Http\Controllers\Tenant\FilePondController;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetController;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetReservationStoreController;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetServiceController;
use App\Http\Controllers\Tenant\Widgets\WidgetBookingController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains;

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

    // Web
    require base_path('routes/tenant-web.php');
    require base_path('routes/tenant-admin.php');
    // Admin routes
    // Route::name('admin.')->middleware(['auth'])->group(base_path('routes/tenant-admin.php'));

    Route::get('/ares/{ico}', [AresController::class, 'show'])
        ->name('api.ares.show');

    // FilePond Routes
    Route::prefix('filepond')->controller(FilePondController::class)->group(function () {
        Route::post('process', 'process')->name('filepond.process');
        Route::delete('revert', 'revert')->name('filepond.revert');
    });

    Route::get('widget/{property:id}', [WidgetBookingController::class, 'show'])->name('widget.show');

    Route::prefix('check-in/{code}')->name('check-in.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'show'])->name('show');
        Route::post('/guests', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'store'])->name('guests.store');
        Route::put('/guests/{guest}', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'update'])->name('guests.update');
        Route::delete('/guests/{guest}', [\App\Http\Controllers\Tenant\Guest\CheckInController::class, 'destroy'])->name('guests.destroy');
    });

    Route::prefix('api/widgets/{propertyId}')
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
