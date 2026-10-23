<?php

namespace App\Exports;

use App\Enums\CardCategory;
use App\Exports\Sheets\CardsSheet;
use App\Exports\Sheets\FuelingSheet;
use App\Exports\Sheets\SummarySheet;
use App\Exports\Sheets\TankSheet;
use App\Exports\Sheets\TopUpSheet;
use App\Exports\Sheets\UsageSheet;
use App\Exports\Sheets\VehiclesSheet;
use App\Models\Tank;
use Illuminate\Contracts\Container\Container;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * "Export Semua Data": one workbook, nine tabs, one class per tab (section 5.3).
 * If this gets slow with real data in phase 2, only the caller changes: it can
 * queue the same export and notify the user.
 */
class FullExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly ExportContext $context,
        private readonly Container $container,
    ) {}

    /** @return list<BuilderSheet> */
    public function sheets(): array
    {
        $context = $this->context;
        $make = fn (string $sheet, array $parameters = []) => $this->container->make($sheet, ['context' => $context] + $parameters);

        $sheets = [
            $make(SummarySheet::class),
            $make(VehiclesSheet::class),
            $make(CardsSheet::class),
            $make(UsageSheet::class, ['category' => CardCategory::Internal]),
            $make(UsageSheet::class, ['category' => CardCategory::External]),
            $make(FuelingSheet::class),
            $make(TopUpSheet::class),
        ];

        foreach (Tank::query()->orderBy('id')->get() as $index => $tank) {
            $sheets[] = $make(TankSheet::class, ['tank' => $tank, 'number' => $index + 1]);
        }

        return $sheets;
    }
}
