<?php

namespace App\Providers;

use App\States\Booking\Pending;
use App\States\Booking\Confirmed;
use App\States\Booking\CheckedIn;
use App\States\Booking\CheckedOut;
use App\States\Booking\Cancelled;
use App\States\Booking\NoShow;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'pending' => Pending::class,
            'confirmed' => Confirmed::class,
            'checked_in' => CheckedIn::class,
            'checked_out' => CheckedOut::class,
            'cancelled' => Cancelled::class,
            'no_show' => NoShow::class,
        ]);

        RateLimiter::for('widgets', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
        });
    }
}
