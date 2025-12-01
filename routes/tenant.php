<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Admin\Auth\AuthenticatedSessionController;
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

Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
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
    Route::get('forgot-password', [\App\Http\Controllers\Tenant\Admin\Auth\PasswordResetLinkController::class, 'create'])
        ->name('password.request')
        ->middleware('guest');

    Route::post('forgot-password', [\App\Http\Controllers\Tenant\Admin\Auth\PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware('guest');

    Route::get('reset-password/{token}', [\App\Http\Controllers\Tenant\Admin\Auth\NewPasswordController::class, 'create'])
        ->name('password.reset')
        ->middleware('guest');

    Route::post('reset-password', [\App\Http\Controllers\Tenant\Admin\Auth\NewPasswordController::class, 'store'])
        ->name('password.store')
        ->middleware('guest');

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
