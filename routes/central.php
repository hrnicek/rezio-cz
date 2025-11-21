<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\WelcomeController;
use Inertia\Inertia;

Route::get('/', WelcomeController::class)->name('welcome');
