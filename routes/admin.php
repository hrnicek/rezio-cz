<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CleaningTaskController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\SwitchPropertyController;

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

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
    });

require __DIR__ . '/settings.php';
