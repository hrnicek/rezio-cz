<?php

namespace App\Http\Requests\Tenant\Admin\PropertyService;

use App\Enums\ServicePriceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePropertyServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_type' => ['required', new Enum(ServicePriceType::class)],
            'price_amount' => ['required', 'numeric', 'min:0'],
            'max_quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
