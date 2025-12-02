<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\FilePondController;
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

Route::domain('app.rezio.test')->middleware([
    // InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'web',
])->group(function () {

    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(base_path('routes/admin.php'));

    if (file_exists(base_path('routes/client.php'))) {
        require base_path('routes/client.php');
    }

    // Tenant API routes with /api/ prefix
    Route::prefix('api')->group(function () {
        if (file_exists(base_path('routes/tenant_api.php'))) {
            require base_path('routes/tenant_api.php');
        }
    });

    // FilePond Routes
    Route::prefix('filepond')->controller(FilePondController::class)->group(function () {
        Route::post('process', 'process')->name('filepond.process');
        Route::delete('revert', 'revert')->name('filepond.revert');
    });

    Route::get('/', function () {
        return redirect()->route('admin.bookings.index');
    })->middleware('auth');

});
