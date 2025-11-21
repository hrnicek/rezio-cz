<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CleaningTaskController;
use App\Http\Controllers\Admin\BookingController;

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('properties', PropertyController::class);
        Route::resource('properties.seasons', SeasonController::class)->except(['show', 'create', 'edit']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/data', [ReportController::class, 'data'])->name('reports.data');

        Route::get('bookings/export', [BookingController::class, 'export'])->name('bookings.export');
        Route::resource('bookings', BookingController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('cleaning-tasks', CleaningTaskController::class);
        Route::post('cleaning-tasks/{cleaningTask}/complete', [CleaningTaskController::class, 'complete'])
            ->name('cleaning-tasks.complete');
    });

require __DIR__ . '/settings.php';
