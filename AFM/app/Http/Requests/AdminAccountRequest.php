<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminAccountRequest extends FormRequest
{
    /** Super Admin only; a 403 must come before validation, so it lives here and not in the controller. */
    public function authorize(): bool
    {
        /** @var User|null $target */
        $target = $this->route('user');

        return $target === null
            ? $this->user()->can('create', User::class)
            : $this->user()->can('update', $target);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var User|null $target */
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($target?->id),
            ],
            // Required when creating; resetting a password has its own route.
            'password' => [Rule::requiredIf($target === null), 'nullable', 'string', Password::defaults()],
        ];
    }
}
