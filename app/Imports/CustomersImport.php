<?php

namespace App\Imports;

use App\Models\CRM\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Customer([
            'first_name' => $row['first_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
            'billing_street' => $row['billing_street'] ?? $row['address'] ?? null,
            'billing_city' => $row['billing_city'] ?? $row['city'] ?? null,
            'billing_zip' => $row['billing_zip'] ?? $row['zip'] ?? null,
            'billing_country' => $row['billing_country'] ?? $row['country'] ?? null,
            'company_name' => $row['company_name'] ?? null,
            'ico' => $row['ico'] ?? null,
            'dic' => $row['dic'] ?? $row['vat_id'] ?? null,
            'internal_notes' => $row['internal_notes'] ?? $row['notes'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['required', 'email'],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
