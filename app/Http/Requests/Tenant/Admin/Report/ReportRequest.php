<?php

namespace App\Http\Requests\Tenant\Admin\Report;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'property_id' => ['nullable', 'exists:properties,id'],
        ];
    }
}
