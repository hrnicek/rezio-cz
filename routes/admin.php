<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Admin\ReportController;
use App\Http\Controllers\Tenant\Admin\SeasonController;
use App\Http\Controllers\Tenant\Admin\BookingController;
use App\Http\Controllers\Tenant\Admin\PropertyController;
use App\Http\Controllers\Tenant\Admin\DashboardController;
use App\Http\Controllers\Tenant\Admin\CleaningTaskController;
use App\Http\Controllers\Tenant\Admin\EmailTemplateController;
use App\Http\Controllers\Tenant\Admin\SwitchPropertyController;
use App\Http\Controllers\Tenant\Admin\Settings\ProfileController;
use App\Http\Controllers\Tenant\Admin\Settings\PasswordController;
use App\Http\Controllers\Tenant\Admin\Settings\TwoFactorAuthenticationController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/switch-property', SwitchPropertyController::class)->name('switch-property');

Route::resource('properties', PropertyController::class);
Route::resource('properties.seasons', SeasonController::class)->except(['show', 'create', 'edit']);
Route::resource('properties.services', \App\Http\Controllers\Admin\PropertyServiceController::class)->except(['show', 'create', 'edit']);
Route::resource('properties.email-templates', EmailTemplateController::class)->only(['index', 'store', 'update']); // Added this line

Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('reports/data', [ReportController::class, 'data'])->name('reports.data');

Route::get('bookings/export', [BookingController::class, 'export'])->name('bookings.export');
Route::resource('bookings', BookingController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

Route::resource('cleaning-tasks', CleaningTaskController::class);
Route::post('cleaning-tasks/{cleaningTask}/complete', [CleaningTaskController::class, 'complete'])
    ->name('cleaning-tasks.complete');

Route::redirect('settings', '/settings/profile');

Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

Route::put('settings/password', [PasswordController::class, 'update'])
    ->middleware('throttle:6,1')
    ->name('user-password.update');

Route::get('settings/appearance', function () {
    return Inertia::render('Admin/Settings/Appearance');
})->name('appearance.edit');

Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
    ->name('two-factor.show');
