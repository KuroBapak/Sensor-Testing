<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Upload rules shared by both Excel imports (section 5.1). */
class ImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx', 'max:2048'],
        ];
    }
}
