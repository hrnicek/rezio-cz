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
            $customer->address,
            $customer->city,
            $customer->zip,
            $customer->country,
            $customer->company_name,
            $customer->vat_id,
            $customer->status,
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
            'Address',
            'City',
            'ZIP',
            'Country',
            'Company Name',
            'VAT ID',
            'Status',
            'Created At',
        ];
    }
}
