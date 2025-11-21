<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class CheckAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'selections' => ['required', 'array'],
            'selections.*.extra_id' => ['required_without:selections.*.service_id', 'integer', 'exists:services,id'],
            'selections.*.service_id' => ['required_without:selections.*.extra_id', 'integer', 'exists:services,id'],
            'selections.*.quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
