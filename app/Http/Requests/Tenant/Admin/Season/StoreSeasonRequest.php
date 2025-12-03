<?php

namespace App\Http\Requests\Tenant\Admin\Season;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'start_date' => ['exclude_if:is_default,true', 'required', 'date'],
            'end_date' => ['exclude_if:is_default,true', 'required', 'date', 'after:start_date'],
            'price' => ['required', 'numeric', 'min:0'],
            'min_stay' => ['nullable', 'integer', 'min:1'],
            'min_persons' => ['nullable', 'integer', 'min:1'],
            'priority' => ['nullable', 'integer'],
            'is_recurring' => ['boolean'],
            'is_full_season_booking_only' => ['boolean'],
        ];
    }
}
