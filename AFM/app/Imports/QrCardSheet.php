<?php

namespace App\Imports;

use App\Enums\CardCategory;
use App\Enums\Validity;
use App\Models\QrCard;
use App\Models\VehicleType;
use App\Services\CardService;

/** The "Kartu QR" sheet of a Batch Add: checks its rows; BatchAddImporter stores them. */
class QrCardSheet
{
    public const NAME = 'Kartu QR';

    public const HEADINGS = ['Kategori', 'Kendaraan', 'Nomor Plat', 'Masa Berlaku', 'Hari Berlaku', 'Reset (Ya/Tidak)'];

    public const COLUMNS = [
        'category' => ['Kategori'],
        'vehicle' => ['Kendaraan', 'Tipe Kendaraan'],
        'plate' => ['Nomor Plat', 'Plat'],
        'validity' => ['Masa Berlaku', 'Validitas'],
        'days' => ['Hari Berlaku', 'Jumlah Hari'],
        'reset' => ['Reset (Ya/Tidak)', 'Reset'],
    ];

    /** Dropdown lists in the example: column index => allowed values. */
    public const DROPDOWNS = [0 => ['Internal', 'Eksternal'], 3 => ['Lifetime', 'Sementara'], 5 => ['Ya', 'Tidak']];

    private const CATEGORIES = ['internal' => CardCategory::Internal, 'eksternal' => CardCategory::External, 'external' => CardCategory::External];

    private const VALIDITIES = ['lifetime' => Validity::Lifetime, 'sementara' => Validity::Temporary, 'temporary' => Validity::Temporary];

    /**
     * A card may use a vehicle type that already exists or one that the same file
     * adds on its "Kendaraan" sheet.
     *
     * @param  list<array{row: int, cells: array<string, string>}>  $rows
     * @param  array<string, true>  $typesInFile  lower-case names on the Kendaraan sheet
     * @return array{valid: list<array<string, mixed>>, errors: list<array{sheet: string, row: int, column: string, message: string}>}
     */
    public function check(array $rows, array $typesInFile = []): array
    {
        $known = VehicleType::query()->pluck('name')->mapWithKeys(fn ($name) => [mb_strtolower($name) => true])->all() + $typesInFile;
        $platesInUse = QrCard::query()->notExpired()->pluck('plate_number')->mapWithKeys(fn ($plate) => [$plate => true])->all();
        $seenPlates = [];
        $errors = [];
        $valid = [];

        foreach ($rows as ['row' => $row, 'cells' => $c]) {
            $before = count($errors);
            $add = function (string $column, string $message) use (&$errors, $row) {
                $errors[] = SpreadsheetReader::error(self::NAME, $row, $column, $message);
            };

            $category = self::CATEGORIES[mb_strtolower($c['category'])] ?? null;
            if ($c['category'] === '') {
                $add('Kategori', __('fuel.import.required', ['column' => 'Kategori']));
            } elseif ($category === null) {
                $add('Kategori', __('fuel.import.invalid_choice', ['column' => 'Kategori', 'value' => $c['category'], 'choices' => 'Internal, Eksternal']));
            }

            if ($c['vehicle'] === '') {
                $add('Kendaraan', __('fuel.import.required', ['column' => 'Kendaraan']));
            } elseif (! isset($known[mb_strtolower($c['vehicle'])])) {
                $add('Kendaraan', __('fuel.import.vehicle_not_found', ['name' => $c['vehicle']]));
            }

            $plate = CardService::normalizePlate($c['plate']);
            if ($plate === '') {
                $add('Nomor Plat', __('fuel.import.required', ['column' => 'Nomor Plat']));
            } elseif (mb_strlen($plate) > 20) {
                $add('Nomor Plat', __('fuel.import.too_long', ['column' => 'Nomor Plat', 'max' => 20]));
            } elseif (isset($seenPlates[$plate])) {
                $add('Nomor Plat', __('fuel.import.duplicate_in_file', ['value' => $plate, 'row' => $seenPlates[$plate]]));
            } elseif (isset($platesInUse[$plate])) {
                $add('Nomor Plat', __('fuel.card.plate_taken', ['plate' => $plate]));
            } else {
                $seenPlates[$plate] = $row;
            }

            $validity = self::VALIDITIES[mb_strtolower($c['validity'])] ?? null;
            $days = null;
            if ($c['validity'] === '') {
                $add('Masa Berlaku', __('fuel.import.required', ['column' => 'Masa Berlaku']));
            } elseif ($validity === null) {
                $add('Masa Berlaku', __('fuel.import.invalid_choice', ['column' => 'Masa Berlaku', 'value' => $c['validity'], 'choices' => 'Lifetime, Sementara']));
            } elseif ($validity === Validity::Temporary) {
                if (! ctype_digit($c['days']) || (int) $c['days'] < 1 || (int) $c['days'] > 3650) {
                    $add('Hari Berlaku', __('fuel.import.days', ['column' => 'Hari Berlaku']));
                } else {
                    $days = (int) $c['days'];
                }
            }

            $reset = match (mb_strtolower($c['reset'])) {
                'ya' => true,
                'tidak' => false,
                default => null,
            };
            if ($c['reset'] === '') {
                $add('Reset (Ya/Tidak)', __('fuel.import.required', ['column' => 'Reset (Ya/Tidak)']));
            } elseif ($reset === null) {
                $add('Reset (Ya/Tidak)', __('fuel.import.invalid_choice', ['column' => 'Reset (Ya/Tidak)', 'value' => $c['reset'], 'choices' => 'Ya, Tidak']));
            }

            if (count($errors) === $before) {
                $valid[] = [
                    'category' => $category,
                    'vehicle' => mb_strtolower($c['vehicle']),
                    'plate_number' => $plate,
                    'validity' => $validity,
                    'valid_days' => $days,
                    'reset_enabled' => $reset,
                ];
            }
        }

        return ['valid' => $valid, 'errors' => $errors];
    }
}
