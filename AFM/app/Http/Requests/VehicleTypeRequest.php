<?php

namespace App\Http\Requests;

use App\Models\VehicleType;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class VehicleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var VehicleType|null $current */
        $current = $this->route('vehicleType');

        return [
            'name' => [
                'required', 'string', 'max:100',
                function (string $attribute, mixed $value, Closure $fail) use ($current) {
                    $exists = VehicleType::query()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $value))])
                        ->when($current, fn ($q) => $q->whereKeyNot($current->id))
                        ->exists();

                    if ($exists) {
                        $fail(__('fuel.vehicle.name_taken', ['name' => trim((string) $value)]));
                    }
                },
            ],
            'max_capacity_liters' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'quota_liters' => ['required', 'numeric', 'gt:0', 'max:99999999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }
}
