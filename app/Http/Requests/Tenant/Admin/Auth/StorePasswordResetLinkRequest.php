<?php

namespace App\Http\Requests\Tenant\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StorePasswordResetLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
