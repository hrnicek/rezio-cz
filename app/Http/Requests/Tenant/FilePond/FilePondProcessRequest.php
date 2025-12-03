<?php

namespace App\Http\Requests\Tenant\FilePond;

use Illuminate\Foundation\Http\FormRequest;

class FilePondProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filepond' => ['required', 'file'],
        ];
    }
}
