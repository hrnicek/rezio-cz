<?php

namespace App\Http\Requests\Tenant\Admin\Booking;

use App\States\Booking\BookingState;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', ValidStateRule::make(BookingState::class)],
            'check_in_date' => ['sometimes', 'required', 'date'],
            'check_out_date' => ['sometimes', 'required', 'date', 'after:check_in_date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
