<?php

namespace App\Enums;

enum ResetPeriod: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';

    /** Label as used in dropdowns: Hari, Minggu, Bulan. */
    public function label(): string
    {
        return match ($this) {
            self::Day => 'Hari',
            self::Week => 'Minggu',
            self::Month => 'Bulan',
        };
    }

    /** Lower-case unit used in sentences and column headers: "per hari". */
    public function unit(): string
    {
        return strtolower($this->label());
    }
}
