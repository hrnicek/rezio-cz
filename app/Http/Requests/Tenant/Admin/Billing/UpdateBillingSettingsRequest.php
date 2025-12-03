<?php

namespace App\Http\Requests\Tenant\Admin\Billing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_vat_payer' => ['boolean'],
            'ico' => ['nullable', 'string', 'max:255'],
            'dic' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
            'default_note' => ['nullable', 'string'],

            'bank_account' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:255'],
            'swift' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'show_bank_account' => ['boolean'],

            'proforma_prefix' => ['nullable', 'string', 'max:20'],
            'proforma_current_sequence' => ['nullable', 'integer'],
            'invoice_prefix' => ['nullable', 'string', 'max:20'],
            'invoice_current_sequence' => ['nullable', 'integer'],
            'receipt_prefix' => ['nullable', 'string', 'max:20'],
            'receipt_current_sequence' => ['nullable', 'integer'],

            'due_days' => ['nullable', 'integer'],
        ];
    }
}
