<?php

use Doctrine\Inflector\Rules\Word;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ValidateWidgetCors;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenant\Widgets\WidgetBookingController;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetServiceController;
use App\Http\Controllers\Tenant\Widgets\Api\WidgetReservationStoreController;

Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'web',
])->group(function () {
Route::name('client.')
    ->group(function () {
        Route::middleware(['allow-iframe'])->group(function () {
            Route::get('app/widget/{property:id}', [WidgetBookingController::class, 'show'])->name('widget.show');
        });
    });
});

Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'api'
])->group(function () {

    Route::prefix('api/widgets/{id}')->group(function () {
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
