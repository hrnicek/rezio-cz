<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Customer::all();
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->first_name,
            $customer->last_name,
            $customer->email,
            $customer->phone,
            $customer->billing_street, // address was ambiguous, using billing_street as per new schema
            $customer->billing_city,
            $customer->billing_zip,
            $customer->billing_country,
            $customer->company_name,
            $customer->ico, // vat_id -> ico/dic usually, checking Customer model for exact field names
            $customer->dic,
            // $customer->status, // Status removed
            $customer->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Billing Street',
            'Billing City',
            'Billing ZIP',
            'Billing Country',
            'Company Name',
            'ICO',
            'DIC',
            // 'Status',
            'Created At',
        ];
    }
}
