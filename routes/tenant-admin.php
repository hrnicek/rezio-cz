<?php

use App\Http\Controllers\Tenant\Admin\BillingController;
use App\Http\Controllers\Tenant\Admin\BookingController;
use App\Http\Controllers\Tenant\Admin\CalendarController;
use App\Http\Controllers\Tenant\Admin\DashboardController;
use App\Http\Controllers\Tenant\Admin\EmailTemplateController;
use App\Http\Controllers\Tenant\Admin\PropertyController;
use App\Http\Controllers\Tenant\Admin\ReportController;
use App\Http\Controllers\Tenant\Admin\SeasonController;
use App\Http\Controllers\Tenant\Admin\Settings\PasswordController;
use App\Http\Controllers\Tenant\Admin\Settings\ProfileController;
use App\Http\Controllers\Tenant\Admin\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::name('admin.')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::get('properties/{property}/billing', [BillingController::class, 'edit'])->name('properties.billing.edit');
    Route::put('properties/{property}/billing', [BillingController::class, 'update'])->name('properties.billing.update');

    Route::resource('properties', PropertyController::class);
    Route::resource('properties.seasons', SeasonController::class)->except(['show', 'create', 'edit']);
    Route::get('properties/{property}/bookings/export', [\App\Http\Controllers\Tenant\Admin\PropertyBookingController::class, 'export'])->name('properties.bookings.export');
    Route::resource('properties.bookings', \App\Http\Controllers\Tenant\Admin\PropertyBookingController::class)->only(['index']);
    Route::resource('properties.services', \App\Http\Controllers\Tenant\Admin\PropertyServiceController::class)->except(['show', 'create', 'edit']);
    Route::resource('properties.email-templates', EmailTemplateController::class)->only(['index', 'store', 'update']);

    Route::get('customers/export', [\App\Http\Controllers\Tenant\Admin\CustomerController::class, 'export'])->name('customers.export');
    Route::post('customers/import', [\App\Http\Controllers\Tenant\Admin\CustomerController::class, 'import'])->name('customers.import');
    Route::resource('customers', \App\Http\Controllers\Tenant\Admin\CustomerController::class);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/data', [ReportController::class, 'data'])->name('reports.data');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('bookings/export', [BookingController::class, 'export'])->name('bookings.export');
    Route::resource('bookings', BookingController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('bookings.payments', \App\Http\Controllers\Tenant\Admin\BookingPaymentController::class)->only(['store', 'destroy']);

    Route::resource('invoices', \App\Http\Controllers\Tenant\Admin\InvoiceController::class)->only(['index']);

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
});
