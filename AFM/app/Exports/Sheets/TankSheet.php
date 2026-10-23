<?php

namespace App\Exports\Sheets;

use App\Exports\BuilderSheet;
use App\Exports\ExportContext;
use App\Exports\SheetBuilder;
use App\Models\Tank;
use App\Services\PeriodService;

/** Hourly readings of one tank in the period. */
class TankSheet extends BuilderSheet
{
    public function __construct(
        private readonly ExportContext $context,
        private readonly Tank $tank,
        private readonly int $number,
        private readonly PeriodService $periods,
    ) {}

    public function title(): string
    {
        return 'Level Tangki '.$this->number;
    }

    protected function build(SheetBuilder $b): void
    {
        $readings = $this->tank->readings()
            ->where('read_at', '>=', $this->periods->toDb($this->context->from))
            ->where('read_at', '<', $this->periods->toDb($this->context->to))
            ->orderBy('read_at')
            ->get();

        $this->context->titleBlock($b, $this->title());
        $b->header(['Waktu', 'Total (%)', 'BBM (%)', 'Air (%)']);

        foreach ($readings as $reading) {
            $b->row([
                SheetBuilder::excelDate($reading->read_at, $this->context->timezone),
                $reading->total_pct,
                $reading->fuel_pct,
                $reading->water_pct,
            ]);
        }

        $b->format(0, SheetBuilder::DATETIME)
            ->format(1, SheetBuilder::PERCENT)
            ->format(2, SheetBuilder::PERCENT)
            ->format(3, SheetBuilder::PERCENT);
    }
}
