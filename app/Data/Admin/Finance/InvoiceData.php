<?php

namespace App\Data\Admin\Finance;

use Spatie\LaravelData\Data;

class InvoiceData extends Data
{
    public function __construct(
        public string $id,
        public string $number,
        public string $issued_at,
        public string $due_at,
        public int $total_amount,
        public string $status,
    ) {}
}
