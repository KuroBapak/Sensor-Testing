<?php

namespace App\Exports\Sheets;

use App\Enums\CardCategory;
use App\Exports\BuilderSheet;
use App\Exports\ExportContext;
use App\Exports\SheetBuilder;
use App\Models\QrCard;
use App\Services\UsageService;

/** Liters used per card in the period, grouped by vehicle type with subtotals and a grand total. */
class UsageSheet extends BuilderSheet
{
    public function __construct(
        private readonly ExportContext $context,
        private readonly CardCategory $category,
        private readonly UsageService $usage,
    ) {}

    public function title(): string
    {
        return 'Pemakaian '.$this->category->label();
    }

    protected function build(SheetBuilder $b): void
    {
        $cards = $this->usage->cardRows($this->category, $this->context->from, $this->context->to);

        $this->context->titleBlock($b, $this->title());
        $b->header(['Nomor Plat', 'Kendaraan', 'Pemakaian (L)', 'Keterangan']);

        foreach ($cards->groupBy('vehicle_type_name') as $typeName => $typeCards) {
            foreach ($typeCards as $card) {
                /** @var QrCard $card */
                $b->row([
                    $card->plate_number,
                    $typeName,
                    $card->used_in_period,
                    $card->deleted_at ? 'Kartu dihapus' : '',
                ], 1);
            }
            $b->row(["Subtotal {$typeName}", '', round($typeCards->sum('used_in_period'), 2), ''], 0, true);
        }

        $b->row(["Total {$this->category->label()}", '', round($cards->sum('used_in_period'), 2), ''], 0, true);
        $b->format(2, SheetBuilder::LITERS);
    }
}
