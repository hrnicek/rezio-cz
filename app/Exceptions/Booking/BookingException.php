<?php

namespace App\Exceptions\Booking;

use Exception;

abstract class BookingException extends Exception
{
    protected array $context = [];

    public function __construct(string $message, array $context = [], int $code = 0, ?\Throwable $previous = null)
    {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'type' => static::class,
            'context' => $this->context,
        ];
    }
}