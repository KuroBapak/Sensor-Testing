<?php

namespace App\Exports\Sheets;

use App\Exports\BuilderSheet;
use App\Exports\ExportContext;
use App\Exports\SheetBuilder;
use App\Models\QuotaAdjustment;
use App\Services\PeriodService;

class TopUpSheet extends BuilderSheet
{
    public function __construct(
        private readonly ExportContext $context,
        private readonly PeriodService $periods,
    ) {}

    public function title(): string
    {
        return 'Riwayat Top-up';
    }

    protected function build(SheetBuilder $b): void
    {

        $topUps = QuotaAdjustment::query()
            ->with(['qrCard', 'user'])
            ->where('created_at', '>=', $this->periods->toDb($this->context->from))
            ->where('created_at', '<', $this->periods->toDb($this->context->to))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $this->context->titleBlock($b, 'Riwayat Top-up');
        $b->header(['Waktu', 'Nomor Plat', 'Liter', 'Catatan', 'Pengguna']);

        foreach ($topUps as $topUp) {
            $b->row([
                SheetBuilder::excelDate($topUp->created_at, $this->context->timezone),
                $topUp->qrCard->plate_number,
                $topUp->liters,
                $topUp->note ?? '',
                $topUp->user->name ?? '-',
            ]);
        }

        $b->format(0, SheetBuilder::DATETIME)->format(2, SheetBuilder::LITERS);
    }
}
