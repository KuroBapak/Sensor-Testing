<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Downloadable import template: Indonesian header row, example rows, and
 * dropdown lists for the columns that only accept fixed values (section 5.1).
 */
class TemplateExport implements FromArray, WithEvents, WithTitle
{
    use Exportable;

    private const MAX_ROWS = 1000;

    /**
     * @param  list<string>  $headings
     * @param  list<list<mixed>>  $examples  rows shown under the header, as a hint for what to enter
     * @param  array<int, list<string>>  $dropdowns  0-based column index => allowed values
     */
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $examples,
        private readonly array $dropdowns = [],
    ) {}

    public function title(): string
    {
        return $this->title;
    }

    /** @return list<list<mixed>> */
    public function array(): array
    {
        return [$this->headings, ...$this->examples];
    }

    /** @return array<class-string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last = Coordinate::stringFromColumnIndex(count($this->headings));

                $sheet->getStyle("A1:{$last}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
                ]);
                $sheet->freezePane('A2');

                foreach ($this->headings as $index => $heading) {
                    $sheet->getColumnDimensionByColumn($index + 1)->setWidth(max(14, mb_strlen($heading) + 4));
                }

                foreach ($this->dropdowns as $index => $values) {
                    $letter = Coordinate::stringFromColumnIndex($index + 1);
                    $validation = new DataValidation;
                    $validation->setType(DataValidation::TYPE_LIST)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(true)
                        ->setShowDropDown(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Nilai tidak valid')
                        ->setError('Pilih salah satu nilai dari daftar.')
                        ->setFormula1('"'.implode(',', $values).'"');
                    $sheet->setDataValidation("{$letter}2:{$letter}".(self::MAX_ROWS + 1), $validation);
                }
            },
        ];
    }
}
