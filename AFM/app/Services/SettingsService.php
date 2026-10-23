<?php

namespace App\Services;

use App\Enums\ResetPeriod;
use App\Models\Setting;
use Illuminate\Container\Attributes\Scoped;

/**
 * Global business settings (section 4.5). Values live in the `settings` table;
 * missing keys fall back to DEFAULTS, so a fresh database already behaves.
 */
#[Scoped]
class SettingsService
{
    public const DEFAULTS = [
        'reset_period' => 'day',
        'reset_time' => '00:00',
        'week_start_day' => 1, // ISO weekday: 1 = Monday ... 7 = Sunday
        'month_reset_day' => 1,
        'timezone' => 'Asia/Jakarta',
        'low_quota_percent' => 20,
        'site_name' => '',
        'logo_path' => '',
    ];

    /** @var array<string, mixed>|null */
    private ?array $values = null;

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->values === null) {
            $stored = Setting::query()->pluck('value', 'key')->all();

            $this->values = [];
            foreach (self::DEFAULTS as $key => $default) {
                $this->values[$key] = array_key_exists($key, $stored)
                    ? $this->cast($default, $stored[$key])
                    : $default;
            }
        }

        return $this->values;
    }

    public function get(string $key): mixed
    {
        return $this->all()[$key] ?? self::DEFAULTS[$key] ?? null;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function update(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! array_key_exists($key, self::DEFAULTS)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value],
            );
        }

        $this->flush();
    }

    public function flush(): void
    {
        $this->values = null;
    }

    public function resetPeriod(): ResetPeriod
    {
        return ResetPeriod::from($this->get('reset_period'));
    }

    public function timezone(): string
    {
        return (string) $this->get('timezone');
    }

    /** @return array{0: int, 1: int} hour and minute of the reset time */
    public function resetTime(): array
    {
        [$hour, $minute] = array_map('intval', explode(':', (string) $this->get('reset_time')) + [0, 0]);

        return [$hour, $minute];
    }

    private function cast(mixed $default, ?string $stored): mixed
    {
        return match (true) {
            is_bool($default) => filter_var($stored, FILTER_VALIDATE_BOOLEAN),
            is_int($default) => (int) $stored,
            default => (string) $stored,
        };
    }
}
