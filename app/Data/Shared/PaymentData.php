<?php

namespace App\Data\Shared;

use Spatie\LaravelData\Data;

class PaymentData extends Data
{
    public function __construct(
        public int $id,
        public int $amount,
        public string $payment_method,
        public ?string $paid_at,
        public string $status,
    ) {}

    public static function fromModel($payment): self
    {
        return new self(
            id: $payment->id,
            amount: (int) $payment->amount,
            payment_method: $payment->payment_method,
            paid_at: $payment?->paid_at?->toISOString(),
            status: $payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status,
        );
    }
}
