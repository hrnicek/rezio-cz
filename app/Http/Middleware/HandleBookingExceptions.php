<?php

namespace App\Http\Middleware;

use App\Exceptions\Booking\BookingException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleBookingExceptions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (BookingException $e) {
            // Log booking-specific errors with context
            Log::warning('Booking exception caught by middleware', [
                'exception_type' => get_class($e),
                'message' => $e->getMessage(),
                'context' => $e->getContext(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);

            // Return appropriate JSON response for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                    'context' => config('app.debug') ? $e->getContext() : null,
                ], $e->getCode() ?: 422);
            }

            // For web requests, redirect back with error
            return redirect()->back()
                ->withInput()
                ->withErrors(['booking' => $e->getMessage()]);
        }
    }
}