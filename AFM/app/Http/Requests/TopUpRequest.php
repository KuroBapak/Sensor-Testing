<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'liters' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
