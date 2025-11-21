<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\BookingController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

use App\Http\Controllers\DashboardController;

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

use App\Http\Controllers\PropertyController;

Route::resource('properties', PropertyController::class)
    ->middleware(['auth', 'verified']);

use App\Http\Controllers\SeasonalPriceController;

Route::resource('properties.seasonal-prices', SeasonalPriceController::class)
    ->middleware(['auth', 'verified']);

use App\Http\Controllers\ReportController;

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware(['auth', 'verified']);
Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data')->middleware(['auth', 'verified']);

Route::get('/bookings/export', [BookingController::class, 'export'])->name('bookings.export')->middleware(['auth', 'verified']);
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index')->middleware(['auth', 'verified']);
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store')->middleware(['auth', 'verified']);
Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update')->middleware(['auth', 'verified']);
Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy')->middleware(['auth', 'verified']);

use App\Http\Controllers\BookingWidgetController;

Route::get('/book/{token}', [BookingWidgetController::class, 'show'])->name('widget.show');
Route::post('/book/{token}', [BookingWidgetController::class, 'store'])->name('widget.store');

use App\Http\Controllers\CleaningTaskController;

Route::resource('cleaning-tasks', CleaningTaskController::class)
    ->middleware(['auth', 'verified']);

Route::post('cleaning-tasks/{cleaningTask}/complete', [CleaningTaskController::class, 'complete'])
    ->middleware(['auth', 'verified'])
    ->name('cleaning-tasks.complete');

// Include settings routes
require __DIR__ . '/settings.php';
