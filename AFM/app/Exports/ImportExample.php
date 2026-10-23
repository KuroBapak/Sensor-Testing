<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * "Contoh Batch Add": a workbook a client can copy to add vehicles and QR cards in
 * batch. The Kendaraan and Kartu QR sheets come first and are filled in; the
 * Petunjuk sheet explains the steps and every column.
 */
class ImportExample implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly TemplateExport $vehicles,
        private readonly TemplateExport $cards,
        private readonly InstructionsSheet $instructions,
    ) {}

    /** @return list<TemplateExport|InstructionsSheet> */
    public function sheets(): array
    {
        return [$this->vehicles, $this->cards, $this->instructions];
    }
}
