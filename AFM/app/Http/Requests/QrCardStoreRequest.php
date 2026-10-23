<?php

namespace App\Http\Requests;

use App\Enums\CardCategory;
use App\Enums\Validity;
use App\Services\CardService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QrCardStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(CardService $cards): array
    {
        return [
            'category' => ['required', Rule::enum(CardCategory::class)],
            'vehicle_type_id' => ['required', 'integer', Rule::exists('vehicle_types', 'id')->whereNull('deleted_at')],
            'plate_number' => [
                'required', 'string', 'max:20',
                function (string $attribute, mixed $value, Closure $fail) use ($cards) {
                    if ($cards->plateInUse((string) $value)) {
                        $fail(__('fuel.card.plate_taken', ['plate' => CardService::normalizePlate((string) $value)]));
                    }
                },
            ],
            'validity' => ['required', Rule::enum(Validity::class)],
            'valid_days' => [
                Rule::requiredIf(fn () => $this->input('validity') === Validity::Temporary->value),
                'nullable', 'integer', 'min:1', 'max:3650',
            ],
            'reset_enabled' => ['required', 'boolean'],
        ];
    }
}
