<?php

use App\Http\Controllers\Tenant\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Tenant\Admin\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

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
