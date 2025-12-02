<?php

namespace App\Data\Admin\Finance;

use App\Data\Shared\MoneyData;
use Spatie\LaravelData\Data;

class InvoiceData extends Data
{
    public function __construct(
        public string $id,
        public string $number,
        public string $issued_at,
        public string $due_at,
        public MoneyData $total_amount,
        public string $status,
    ) {}

    public static function fromModel($invoice): self
    {
        return new self(
            id: $invoice->id,
            number: $invoice->number,
            issued_at: $invoice->issued_date->format('Y-m-d'),
            due_at: $invoice->due_date->format('Y-m-d'),
            total_amount: MoneyData::fromModel($invoice->total_price_amount, $invoice->currency),
            status: (string) $invoice->status,
        );
    }
}
