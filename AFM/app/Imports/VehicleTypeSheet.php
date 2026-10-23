<?php

namespace App\Imports;

use App\Models\VehicleType;

/** The "Kendaraan" sheet of a Batch Add: checks its rows; BatchAddImporter stores them. */
class VehicleTypeSheet
{
    public const NAME = 'Kendaraan';

    public const HEADINGS = ['Nama Kendaraan', 'Kapasitas Maksimal (L)', 'Kuota BBM (L)'];

    public const COLUMNS = [
        'name' => ['Nama Kendaraan', 'Kendaraan', 'Nama'],
        'capacity' => ['Kapasitas Maksimal (L)', 'Kapasitas Maksimal', 'Kapasitas'],
        'quota' => ['Kuota BBM (L)', 'Kuota BBM', 'Kuota'],
    ];

    /**
     * @param  list<array{row: int, cells: array<string, string>}>  $rows
     * @return array{valid: list<array{name: string, max_capacity_liters: float, quota_liters: float}>, errors: list<array{sheet: string, row: int, column: string, message: string}>}
     */
    public function check(array $rows): array
    {
        $existing = VehicleType::query()->pluck('name')->mapWithKeys(fn ($name) => [mb_strtolower($name) => true])->all();
        $seen = [];
        $errors = [];
        $valid = [];

        foreach ($rows as ['row' => $row, 'cells' => $cells]) {
            $before = count($errors);
            $name = $cells['name'];

            if ($name === '') {
                $errors[] = $this->error($row, 'Nama Kendaraan', __('fuel.import.required', ['column' => 'Nama Kendaraan']));
            } elseif (mb_strlen($name) > 100) {
                $errors[] = $this->error($row, 'Nama Kendaraan', __('fuel.import.too_long', ['column' => 'Nama Kendaraan', 'max' => 100]));
            } elseif (isset($existing[mb_strtolower($name)])) {
                $errors[] = $this->error($row, 'Nama Kendaraan', __('fuel.vehicle.name_taken', ['name' => $name]));
            } elseif (isset($seen[mb_strtolower($name)])) {
                $errors[] = $this->error($row, 'Nama Kendaraan', __('fuel.import.duplicate_in_file', ['value' => $name, 'row' => $seen[mb_strtolower($name)]]));
            } else {
                $seen[mb_strtolower($name)] = $row;
            }

            $capacity = $this->positiveNumber($cells['capacity']);
            if ($capacity === null) {
                $errors[] = $this->error($row, 'Kapasitas Maksimal (L)', __('fuel.import.positive_number', ['column' => 'Kapasitas Maksimal (L)']));
            }

            $quota = $this->positiveNumber($cells['quota']);
            if ($quota === null) {
                $errors[] = $this->error($row, 'Kuota BBM (L)', __('fuel.import.positive_number', ['column' => 'Kuota BBM (L)']));
            }

            if (count($errors) === $before && $capacity !== null && $quota !== null) {
                $valid[] = ['name' => $name, 'max_capacity_liters' => $capacity, 'quota_liters' => $quota];
            }
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    private function positiveNumber(string $value): ?float
    {
        $value = str_replace(',', '.', $value);

        return is_numeric($value) && (float) $value > 0 && (float) $value <= 99999999 ? (float) $value : null;
    }

    /** @return array{sheet: string, row: int, column: string, message: string} */
    private function error(int $row, string $column, string $message): array
    {
        return SpreadsheetReader::error(self::NAME, $row, $column, $message);
    }
}
