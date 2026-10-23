<?php

namespace App\Http\Requests;

use App\Enums\ResetPeriod;
use App\Models\Tank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsUpdateRequest extends FormRequest
{
    /** Time zones offered on the settings page. */
    public const TIMEZONES = [
        'Asia/Jakarta' => 'WIB (Asia/Jakarta)',
        'Asia/Makassar' => 'WITA (Asia/Makassar)',
        'Asia/Jayapura' => 'WIT (Asia/Jayapura)',
    ];

    /** Largest logo upload, in kilobytes. */
    public const LOGO_MAX_KB = 1024;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reset_period' => ['required', Rule::enum(ResetPeriod::class)],
            'reset_time' => ['required', 'date_format:H:i'],
            'week_start_day' => ['required', 'integer', 'between:1,7'],
            'month_reset_day' => ['required', 'integer', 'between:1,28'],
            'timezone' => ['required', Rule::in(array_keys(self::TIMEZONES))],
            'low_quota_percent' => ['required', 'integer', 'between:1,100'],
            'site_name' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:'.self::LOGO_MAX_KB],
            'remove_logo' => ['sometimes', 'boolean'],
            'tanks' => ['required', 'array', 'size:'.Tank::query()->count()],
            'tanks.*.id' => ['required', 'integer', 'exists:tanks,id'],
            'tanks.*.capacity_liters' => ['required', 'numeric', 'gt:0', 'max:9999999'],
            'confirm_reset_change' => ['sometimes', 'boolean'],
        ];
    }
}
