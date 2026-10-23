<?php

namespace App\Exports;

use App\Models\VehicleType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;

/** Quick export of the Vehicles page (section 5.2). */
class VehicleTypesExport extends BuilderSheet
{
    use Exportable;

    /** @param Collection<int, VehicleType> $types */
    public function __construct(private readonly Collection $types) {}

    public function title(): string
    {
        return 'Kendaraan';
    }

    protected function build(SheetBuilder $builder): void
    {
        $builder->header(['Kendaraan', 'Kapasitas Maksimal (L)', 'Kuota BBM (L)', 'Jumlah Kartu QR']);

        foreach ($this->types as $type) {
            $builder->row([$type->name, $type->max_capacity_liters, $type->quota_liters, (int) $type->qr_cards_count]);
        }

        $builder->format(1, SheetBuilder::LITERS)
            ->format(2, SheetBuilder::LITERS)
            ->format(3, SheetBuilder::INTEGER);
    }
}
