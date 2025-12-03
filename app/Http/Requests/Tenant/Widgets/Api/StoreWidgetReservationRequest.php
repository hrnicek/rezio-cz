<?php

namespace App\Http\Requests\Tenant\Widgets\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreWidgetReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'customer' => ['required', 'array'],
            'customer.first_name' => ['required', 'string', 'min:2'],
            'customer.last_name' => ['required', 'string', 'min:2'],
            'customer.email' => ['required', 'email'],
            'customer.phone' => ['required', 'string'],
            'customer.note' => ['nullable', 'string'],
            'customer.is_company' => ['boolean'],
            'customer.company_name' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.ico' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.dic' => ['nullable', 'string'],
            'customer.has_vat' => ['boolean'],
            'customer.billing_street' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.billing_city' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.billing_zip' => ['nullable', 'string', 'required_if:customer.is_company,true'],
            'customer.billing_country' => ['nullable', 'string'],
            'addons' => ['array'],
            'addons.*.service_id' => ['required', 'integer'],
            'addons.*.quantity' => ['required', 'integer', 'min:0'],
            'guests_count' => ['integer', 'min:1', 'max:50'],
        ];
    }
}
