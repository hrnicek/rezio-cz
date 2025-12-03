<?php

namespace App\Http\Requests\Tenant\Admin\Booking;

use App\States\Booking\BookingState;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\ModelStates\Validation\ValidStateRule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => ['required', 'exists:properties,id'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'status' => ['required', ValidStateRule::make(BookingState::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
