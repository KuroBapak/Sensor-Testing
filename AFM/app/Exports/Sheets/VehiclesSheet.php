<?php

namespace App\Exports\Sheets;

use App\Exports\BuilderSheet;
use App\Exports\ExportContext;
use App\Exports\SheetBuilder;
use App\Models\VehicleType;
use App\Services\SettingsService;

class VehiclesSheet extends BuilderSheet
{
    public function __construct(
        private readonly ExportContext $context,
        private readonly SettingsService $settings,
    ) {}

    public function title(): string
    {
        return 'Kendaraan';
    }

    protected function build(SheetBuilder $b): void
    {
        $unit = $this->settings->resetPeriod()->unit();

        $this->context->titleBlock($b, 'Kendaraan');
        $b->header(['Kendaraan', 'Kapasitas Maksimal (L)', "Kuota BBM (L / {$unit})", 'Jumlah Kartu QR']);

        foreach (VehicleType::query()->withCount('qrCards')->orderBy('name')->get() as $type) {
            $b->row([$type->name, $type->max_capacity_liters, $type->quota_liters, (int) $type->qr_cards_count]);
        }

        $b->format(1, SheetBuilder::LITERS)->format(2, SheetBuilder::LITERS)->format(3, SheetBuilder::INTEGER);
    }
}
