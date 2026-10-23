<?php

namespace App\Exports\Sheets;

use App\Enums\CardCategory;
use App\Exports\BuilderSheet;
use App\Exports\ExportContext;
use App\Exports\QrCardsExport;
use App\Exports\SheetBuilder;
use App\Models\QrCard;
use App\Services\QrCardListing;
use App\Services\QuotaService;

/** All cards, grouped by category and then by vehicle type. */
class CardsSheet extends BuilderSheet
{
    public function __construct(
        private readonly ExportContext $context,
        private readonly QrCardListing $listing,
        private readonly QuotaService $quota,
    ) {}

    public function title(): string
    {
        return 'Kartu QR';
    }

    protected function build(SheetBuilder $b): void
    {
        $listing = $this->listing;
        $unit = $listing->resetPeriod()->unit();

        $rows = $this->quota->query()
            ->orderBy('qr_cards.category')
            ->orderBy('qr_cards.vehicle_type_name')
            ->orderBy('qr_cards.plate_number')
            ->get()
            ->map(fn (QrCard $card) => $listing->present($card))
            ->groupBy('category');

        $this->context->titleBlock($b, 'Kartu QR');
        $b->header(['Nomor Plat', 'Kategori', 'Kendaraan', 'Masa Berlaku', 'Kedaluwarsa', 'Reset', "Kuota (L / {$unit})", 'Sisa (L)', 'Status']);

        foreach (CardCategory::cases() as $category) {
            $cards = $rows->get($category->value);
            if ($cards === null) {
                continue;
            }

            foreach ($cards->groupBy('vehicle_type') as $typeName => $typeCards) {
                foreach ($typeCards as $card) {
                    $b->row(QrCardsExport::cells($card, $this->context->timezone, $unit, $listing), 2);
                }
                $b->row(["Subtotal {$typeName} ({$typeCards->count()} kartu)", '', '', '', '', '', '', $typeCards->sum('remaining'), ''], 1, true);
            }

            $b->row(["Total {$category->label()} ({$cards->count()} kartu)", '', '', '', '', '', '', $cards->sum('remaining'), ''], 0, true);
        }

        $b->format(4, SheetBuilder::DATE)->format(6, SheetBuilder::LITERS)->format(7, SheetBuilder::LITERS);
    }
}
