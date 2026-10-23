<?php

namespace App\Exports\Sheets;

use App\Exports\BuilderSheet;
use App\Exports\ExportContext;
use App\Exports\SheetBuilder;
use App\Models\FuelTransaction;
use App\Services\PeriodService;
use Carbon\CarbonImmutable;

/** Every fueling in the period, grouped by date. Deleted cards stay in the history. */
class FuelingSheet extends BuilderSheet
{
    public function __construct(
        private readonly ExportContext $context,
        private readonly PeriodService $periods,
    ) {}

    public function title(): string
    {
        return 'Riwayat Pengisian';
    }

    protected function build(SheetBuilder $b): void
    {
        $tz = $this->context->timezone;

        $fuelings = FuelTransaction::query()
            ->with('qrCard.vehicleType')
            ->where('dispensed_at', '>=', $this->periods->toDb($this->context->from))
            ->where('dispensed_at', '<', $this->periods->toDb($this->context->to))
            ->orderBy('dispensed_at')
            ->orderBy('id')
            ->get();

        $this->context->titleBlock($b, 'Riwayat Pengisian');
        $b->header(['Waktu', 'Nomor Plat', 'Kendaraan', 'Kategori', 'Liter']);

        $grandTotal = 0.0;
        foreach ($fuelings->groupBy(fn ($tx) => CarbonImmutable::instance($tx->dispensed_at)->setTimezone($tz)->format('d/m/Y')) as $date => $day) {
            foreach ($day as $tx) {
                $b->row([
                    SheetBuilder::excelDate(CarbonImmutable::instance($tx->dispensed_at), $tz),
                    $tx->qrCard->plate_number,
                    $tx->qrCard->vehicleType->name,
                    $tx->qrCard->category->label(),
                    (float) $tx->liters,
                ], 1);
            }
            $dayTotal = round($day->sum('liters'), 2);
            $grandTotal += $dayTotal;
            $b->row(["Total {$date}", '', '', '', $dayTotal], 0, true);
        }

        $b->row(['Total Periode', '', '', '', round($grandTotal, 2)], 0, true);
        $b->format(0, SheetBuilder::DATETIME)->format(4, SheetBuilder::LITERS);
    }
}
