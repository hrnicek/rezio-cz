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

Route::get('/bookings/export', [BookingController::class, 'export'])->name('bookings.export');
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

use App\Http\Controllers\BookingWidgetController;

Route::get('/book/{token}', [BookingWidgetController::class, 'show'])->name('widget.show');
Route::post('/book/{token}', [BookingWidgetController::class, 'store'])->name('widget.store');

require __DIR__ . '/settings.php';
