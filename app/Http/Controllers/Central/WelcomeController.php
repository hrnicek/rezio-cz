<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return Inertia::render('Central/Welcome', [
            'canLogin' => \Illuminate\Support\Facades\Route::has('login'),
            'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
        ]);
    }
}
