<?php

namespace App\Exports;

use Carbon\CarbonInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Collects the rows of one worksheet together with the formatting that belongs
 * to them (title block, header rows, outline levels, subtotal rows, number
 * formats), then applies it all in one pass. Every export tab uses the same
 * builder so the formatting standards of section 5.3 hold for all of them.
 */
class SheetBuilder
{
    public const LITERS = '#,##0.0';

    public const PERCENT = '0.0';

    public const DATETIME = 'dd/mm/yyyy hh:mm';

    public const DATE = 'dd/mm/yyyy';

    public const INTEGER = '0';

    private const HEADER_FILL = '1F4E79';

    private const SECTION_FILL = 'DDEBF7';

    private const SUBTOTAL_FILL = 'F2F2F2';

    /** @var list<list<mixed>> */
    private array $rows = [];

    private ?int $headerRow = null;

    /** @var list<int> */
    private array $headerRows = [];

    /** @var list<int> */
    private array $sectionRows = [];

    /** @var list<int> */
    private array $subtotalRows = [];

    /** @var array<int, int> */
    private array $levels = [];

    /** @var array<int, array<int, string>> */
    private array $cellFormats = [];

    /** @var array<int, string> */
    private array $columnFormats = [];

    private int $titleRowCount = 0;

    private bool $autoFilter = true;

    private bool $wrapTable = false;

    /** @var list<int> */
    private array $paragraphRows = [];

    /** Quick exports have no title block: the header is the first row. */
    public function titleBlock(string $title, string $period, string $exportedAt, ?string $site = null): static
    {
        $this->rows[] = [$site ? $title.' — '.$site : $title];
        $this->rows[] = ['Periode: '.$period];
        $this->rows[] = ['Diekspor: '.$exportedAt];
        $this->rows[] = [null]; // an empty array would be dropped by Laravel Excel and shift every row index
        $this->titleRowCount = 3;

        return $this;
    }

    /** @param list<string> $columns */
    public function header(array $columns): static
    {
        $this->rows[] = $columns;
        $row = count($this->rows);
        $this->headerRows[] = $row;
        $this->headerRow ??= $row;

        return $this;
    }

    /** A bold label row that opens a block, used by the summary tab. */
    public function section(string $label): static
    {
        $this->rows[] = [$label];
        $this->sectionRows[] = count($this->rows);

        return $this;
    }

    /**
     * A sentence or two spread over the full width of the sheet (merged and
     * wrapped), used by instruction sheets. It is left out of column widths.
     */
    public function paragraph(string $text): static
    {
        $this->rows[] = [$text];
        $this->paragraphRows[] = count($this->rows);

        return $this;
    }

    /** Wrap the text of every table cell, for sheets that hold sentences. */
    public function wrapTable(): static
    {
        $this->wrapTable = true;

        return $this;
    }

    public function blank(): static
    {
        $this->rows[] = [null];

        return $this;
    }

    /**
     * @param  list<mixed>  $values
     * @param  int  $level  outline level; detail rows of a group use 1 (or 2 for nested groups)
     * @param  bool  $subtotal  bold row with a fill, placed below the rows it sums
     * @param  array<int, string>  $formats  number format per column index for this row only
     */
    public function row(array $values, int $level = 0, bool $subtotal = false, array $formats = []): static
    {
        $this->rows[] = $values;
        $row = count($this->rows);

        if ($level > 0) {
            $this->levels[$row] = $level;
        }
        if ($subtotal) {
            $this->subtotalRows[] = $row;
        }
        if ($formats) {
            $this->cellFormats[$row] = $formats;
        }

        return $this;
    }

    /** Number format for a whole column (0-based index) below the first header row. */
    public function format(int $column, string $code): static
    {
        $this->columnFormats[$column] = $code;

        return $this;
    }

    public function withoutAutoFilter(): static
    {
        $this->autoFilter = false;

        return $this;
    }

    /** @return list<list<mixed>> */
    public function rows(): array
    {
        return $this->rows;
    }

    /** A real Excel date/time cell value for a moment, in the given time zone. */
    public static function excelDate(CarbonInterface $moment, string $timezone): float
    {
        return Date::dateTimeToExcel($moment->setTimezone($timezone)->toDateTime());
    }

    public function apply(Worksheet $sheet): void
    {
        $lastRow = count($this->rows);
        if ($lastRow === 0) {
            return;
        }

        $tableStart = $this->titleRowCount > 0 ? $this->titleRowCount + 2 : 1;
        $lastColumn = max([1, ...array_map('count', array_slice($this->rows, $tableStart - 1))]);
        $lastLetter = Coordinate::stringFromColumnIndex($lastColumn);

        $this->styleTitle($sheet);

        foreach ($this->headerRows as $row) {
            $range = "A{$row}:{$lastLetter}{$row}";
            $sheet->getStyle($range)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_FILL]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
        }

        foreach ($this->sectionRows as $row) {
            $sheet->getStyle("A{$row}:{$lastLetter}{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::SECTION_FILL]],
            ]);
        }

        foreach ($this->subtotalRows as $row) {
            $sheet->getStyle("A{$row}:{$lastLetter}{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::SUBTOTAL_FILL]],
            ]);
        }

        foreach ($this->levels as $row => $level) {
            $sheet->getRowDimension($row)->setOutlineLevel($level);
        }
        $sheet->setShowSummaryBelow(true);

        $first = ($this->headerRow ?? 0) + 1;
        foreach ($this->columnFormats as $column => $code) {
            $letter = Coordinate::stringFromColumnIndex($column + 1);
            $sheet->getStyle("{$letter}{$first}:{$letter}{$lastRow}")->getNumberFormat()->setFormatCode($code);
        }
        foreach ($this->cellFormats as $row => $formats) {
            foreach ($formats as $column => $code) {
                $letter = Coordinate::stringFromColumnIndex($column + 1);
                $sheet->getStyle("{$letter}{$row}")->getNumberFormat()->setFormatCode($code);
            }
        }

        $this->fitColumns($sheet, $tableStart, $lastColumn);

        if ($this->wrapTable) {
            $sheet->getStyle("A1:{$lastLetter}{$lastRow}")->getAlignment()
                ->setWrapText(true)
                ->setVertical(Alignment::VERTICAL_TOP);
        }

        foreach ($this->paragraphRows as $row) {
            $sheet->mergeCells("A{$row}:{$lastLetter}{$row}");
            $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            // Excel does not fit the height of merged cells, so estimate it from the text length.
            $characters = mb_strlen((string) $this->rows[$row - 1][0]);
            $sheet->getRowDimension($row)->setRowHeight(15 * max(1, (int) ceil($characters / 105)));
        }

        if ($this->headerRow !== null) {
            $sheet->freezePane('A'.($this->headerRow + 1));
            if ($this->autoFilter) {
                $sheet->setAutoFilter("A{$this->headerRow}:{$lastLetter}{$lastRow}");
            }
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($this->headerRow, $this->headerRow);
        }

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getSheetView()->setZoomScale(100);
        $sheet->getPageSetup()->setFitToPage(true);
    }

    private function styleTitle(Worksheet $sheet): void
    {
        if ($this->titleRowCount === 0) {
            return;
        }

        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => self::HEADER_FILL]]]);
        $sheet->getStyle('A2:A3')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '595959']]]);
    }

    /** Column widths fitted to the table; the title block is ignored so it cannot widen column A. */
    private function fitColumns(Worksheet $sheet, int $tableStart, int $lastColumn): void
    {
        for ($column = 1; $column <= $lastColumn; $column++) {
            $width = 8;
            foreach (array_slice($this->rows, $tableStart - 1, null, true) as $index => $row) {
                if (in_array($index + 1, $this->paragraphRows, true)) {
                    continue;
                }

                $value = $row[$column - 1] ?? null;
                if ($value === null) {
                    continue;
                }
                $length = match (true) {
                    is_float($value) || is_int($value) => 12,
                    default => mb_strlen((string) $value) + 2,
                };
                $width = max($width, min($length, 60));
            }
            $sheet->getColumnDimensionByColumn($column)->setWidth($width);
        }
    }
}
