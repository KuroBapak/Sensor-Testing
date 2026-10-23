<?php

namespace App\Exports;

use App\Services\QrCardListing;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Concerns\Exportable;

/** Quick export of the QR page: the table as currently filtered and sorted (section 5.2). */
class QrCardsExport extends BuilderSheet
{
    use Exportable;

    /**
     * @param  list<array<string, mixed>>  $rows  output of QrCardListing::present(), in table order
     */
    public function __construct(
        private readonly array $rows,
        private readonly QrCardListing $listing,
        private readonly string $timezone,
    ) {}

    public function title(): string
    {
        return 'Kartu QR';
    }

    protected function build(SheetBuilder $builder): void
    {
        $unit = $this->listing->resetPeriod()->unit();

        $builder->header(['Nomor Plat', 'Kategori', 'Kendaraan', 'Masa Berlaku', 'Kedaluwarsa', 'Reset', "Kuota (L / {$unit})", 'Sisa (L)', 'Status']);

        foreach ($this->rows as $row) {
            $builder->row(self::cells($row, $this->timezone, $unit, $this->listing));
        }

        $builder->format(4, SheetBuilder::DATE)
            ->format(6, SheetBuilder::LITERS)
            ->format(7, SheetBuilder::LITERS);
    }

    /**
     * One table row, shared with the QR card tab of the full export.
     *
     * @param  array<string, mixed>  $row
     * @return list<mixed>
     */
    public static function cells(array $row, string $timezone, string $unit, QrCardListing $listing): array
    {
        return [
            $row['plate_number'],
            $row['category_label'],
            $row['vehicle_type'],
            $row['validity_label'],
            $row['expires_at'] ? SheetBuilder::excelDate(CarbonImmutable::parse($row['expires_at']), $timezone) : 'Lifetime',
            $row['reset_enabled'] ? "Ya (per {$unit})" : 'Tidak',
            $row['quota'],
            $row['remaining'],
            implode(', ', $listing->stateLabels($row)),
        ];
    }
}
