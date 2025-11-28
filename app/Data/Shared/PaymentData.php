<?php

namespace App\Data\Shared;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Spatie\LaravelData\Data;

class PaymentData extends Data
{
    public function __construct(
        public string $id,
        public int $amount,
        public PaymentMethod $payment_method,
        public ?string $paid_at,
        public PaymentStatus $status,
    ) {}

    public static function fromModel($payment): self
    {
        return new self(
            id: $payment->id,
            amount: (int) $payment->amount,
            payment_method: $payment->payment_method instanceof \BackedEnum ? $payment->payment_method : PaymentMethod::from($payment->payment_method),
            paid_at: $payment?->paid_at?->toISOString(),
            status: $payment->status instanceof \BackedEnum ? $payment->status : PaymentStatus::from($payment->status),
        );
    }
}
