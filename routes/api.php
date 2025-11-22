<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\Api\ServiceController;
use App\Http\Controllers\Client\Api\BookingController;
use App\Http\Controllers\Client\Api\Service\ListServicesController;

Route::prefix('bookings')->group(function () {
    Route::get('calendar', [BookingController::class, 'calendar'])->name('api.bookings.calendar');
    Route::post('verify', [BookingController::class, 'verify'])->name('api.bookings.verify');
    Route::post('verify-customer', [BookingController::class, 'verifyCustomer'])->name('api.bookings.verify-customer');
    Route::post('/', [BookingController::class, 'store'])->name('api.bookings.store');
});

Route::prefix('services')->group(function () {
    Route::get('/', ListServicesController::class)->name('api.services.index');
    Route::post('availability', [ServiceController::class, 'availability'])->name('api.services.availability');
});

// Property-specific endpoints (using widget token)
Route::prefix('properties/{token}')->group(function () {
    Route::get('services', [ServiceController::class, 'indexByProperty'])->name('api.properties.services');
    Route::post('services/availability', [ServiceController::class, 'availabilityByProperty'])->name('api.properties.services.availability');
    Route::get('calendar', [BookingController::class, 'calendarByProperty'])->name('api.properties.calendar');
    Route::post('verify', [BookingController::class, 'verifyByProperty'])->name('api.properties.verify');
    Route::post('bookings', [BookingController::class, 'storeByProperty'])->name('api.properties.bookings.store');
});
