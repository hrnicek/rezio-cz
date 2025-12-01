<?php

use App\Http\Controllers\Tenant\Api\AresController;
use Illuminate\Support\Facades\Route;

Route::get('/ares/{ico}', [AresController::class, 'show'])->name('api.ares.show');
