<?php

namespace App\Http\Requests\Tenant\Admin\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ImportCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls'],
        ];
    }
}
