<?php

namespace App\Http\Requests\Tenant\Guest\CheckIn;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'is_adult' => ['required', 'boolean'],
            'gender' => ['nullable', 'string'],
            'nationality' => ['nullable', 'string'],
            'document_type' => ['nullable', 'string'],
            'document_number' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string'],
            'address' => ['nullable', 'array'],
            'signature' => ['nullable', 'string'],
        ];
    }
}
