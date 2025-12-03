<?php

namespace App\Services;

use App\Exceptions\Booking\BookingException;
use App\Exceptions\Booking\BookingValidationException;
use App\Models\Booking\Booking;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookingRetryService
{
    private const MAX_RETRIES = 3;

    private const RETRY_DELAY_MS = 100;

    /**
     * Execute a booking operation with retry logic
     *
     * @param  callable  $operation  The booking operation to retry
     * @param  array  $context  Context for logging
     *
     * @throws BookingException
     */
    public function executeWithRetry(callable $operation, array $context = []): Booking
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                return $operation();
            } catch (BookingValidationException $e) {
                // Don't retry business logic exceptions
                throw $e;
            } catch (Throwable $e) {
                $lastException = $e;

                Log::warning("Booking operation failed (attempt {$attempt}/".self::MAX_RETRIES.')', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'context' => $context,
                ]);

                // Don't retry on the last attempt
                if ($attempt < self::MAX_RETRIES) {
                    // Exponential backoff with jitter
                    $delay = self::RETRY_DELAY_MS * pow(2, $attempt - 1);
                    $jitter = random_int(0, 50);
                    usleep(($delay + $jitter) * 1000);
                }
            }
        }

        // If we get here, all retries failed - re-throw the original exception
        // This provides more specific error information to the user
        Log::error('Booking operation failed after all retries', [
            'error' => $lastException->getMessage(),
            'attempts' => self::MAX_RETRIES,
            'context' => $context,
            'trace' => $lastException->getTraceAsString(),
        ]);

        // Re-throw the original exception instead of generic message
        throw $lastException;
    }

    /**
     * Check if an exception is retryable
     */
    public function isRetryable(Throwable $exception): bool
    {
        // Don't retry business logic exceptions
        if ($exception instanceof BookingValidationException) {
            return false;
        }

        // Retry database connection issues, deadlocks, etc.
        $retryableErrors = [
            'deadlock',
            'lock wait timeout',
            'connection refused',
            'server has gone away',
            'lost connection',
        ];

        $message = strtolower($exception->getMessage());

        foreach ($retryableErrors as $error) {
            if (str_contains($message, $error)) {
                return true;
            }
        }

        return false;
    }
}
