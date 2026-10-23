<?php

namespace App\Imports;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Reads an uploaded .xlsx: every sheet as plain rows, and a table inside a sheet
 * mapped by header text, so a user who reorders columns still imports correctly.
 */
class SpreadsheetReader
{
    /** Data rows allowed in one file, all sheets together (section 5.1). */
    public const MAX_ROWS = 1000;

    /**
     * Every sheet of the workbook, header row included, keyed by sheetKey() of its name.
     *
     * @return array{sheets: array<string, list<list<mixed>>>, errors: list<array{sheet: string, row: int, column: string, message: string}>}
     */
    public function open(UploadedFile|string $file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $workbook = $reader->load((string) $path);
        } catch (SpreadsheetException) {
            // Right extension and type, but not a readable workbook (corrupt or truncated).
            return ['sheets' => [], 'errors' => [self::error('-', 1, '-', __('fuel.import.unreadable'))]];
        }

        $sheets = [];
        foreach ($workbook->getAllSheets() as $sheet) {
            $sheets[self::sheetKey($sheet->getTitle())] = array_values(array_map(
                'array_values',
                $sheet->toArray(null, true, false, false),
            ));
        }
        $workbook->disconnectWorksheets();

        return ['sheets' => $sheets, 'errors' => []];
    }

    /**
     * The data rows of one sheet, with the cells named after $columns.
     *
     * @param  list<list<mixed>>  $lines  the sheet with its header row first
     * @param  array<string, list<string>>  $columns  key => accepted header texts (first one is the template header)
     * @return array{rows: list<array{row: int, cells: array<string, string>}>, errors: list<array{sheet: string, row: int, column: string, message: string}>}
     */
    public function table(string $sheet, array $lines, array $columns): array
    {
        $header = array_shift($lines) ?? [];

        $index = [];
        foreach ($header as $position => $text) {
            $index[self::sheetKey((string) $text)] = $position;
        }

        $positions = [];
        $errors = [];
        foreach ($columns as $key => $accepted) {
            $found = null;
            foreach ($accepted as $text) {
                if (array_key_exists(self::sheetKey($text), $index)) {
                    $found = $index[self::sheetKey($text)];
                    break;
                }
            }
            if ($found === null) {
                $errors[] = self::error($sheet, 1, $accepted[0], __('fuel.import.missing_column', ['column' => $accepted[0]]));
            }
            $positions[$key] = $found;
        }

        if ($errors !== []) {
            return ['rows' => [], 'errors' => $errors];
        }

        $rows = [];
        foreach ($lines as $offset => $line) {
            $cells = [];
            foreach ($positions as $key => $position) {
                $cells[$key] = self::cell($line[$position] ?? null);
            }

            if (implode('', $cells) === '') {
                continue; // blank spreadsheet row
            }

            $rows[] = ['row' => $offset + 2, 'cells' => $cells];
        }

        return ['rows' => $rows, 'errors' => []];
    }

    /** @return array{sheet: string, row: int, column: string, message: string} */
    public static function error(string $sheet, int $row, string $column, string $message): array
    {
        return [
            'sheet' => $sheet,
            'row' => $row,
            'column' => $column,
            'message' => __('fuel.import.row', ['row' => $row, 'message' => $message]),
        ];
    }

    /** "Kartu QR" and "kartu qr " are the same sheet; so are "Reset (Ya/Tidak)" and "reset ya tidak". */
    public static function sheetKey(string $text): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower($text)) ?? '';
    }

    private static function cell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }
}
