<?php

namespace App\Providers;

use App\States\Booking\Cancelled;
use App\States\Booking\CheckedIn;
use App\States\Booking\CheckedOut;
use App\States\Booking\Confirmed;
use App\States\Booking\NoShow;
use App\States\Booking\Pending;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
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
        Event::listen(
            \App\Events\Payment\PaymentCreated::class,
            \App\Listeners\Payment\CreatePaymentConfirmationInvoice::class,
        );

        Event::listen(
            \App\Events\Payment\PaymentUpdated::class,
            \App\Listeners\Payment\CreatePaymentConfirmationInvoice::class,
        );

        Event::listen(
            \App\Events\Payment\PaymentDeleted::class,
            \App\Listeners\Payment\HandlePaymentDeleted::class,
        );

        Event::listen(
            \App\Events\Booking\BookingCreated::class,
            \App\Listeners\Booking\CreateProformaInvoice::class,
        );

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
