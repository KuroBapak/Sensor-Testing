<?php

namespace App\Exports\Sheets;

use App\Enums\CardCategory;
use App\Enums\ResetPeriod;
use App\Exports\BuilderSheet;
use App\Exports\ExportContext;
use App\Exports\SheetBuilder;
use App\Models\Tank;
use App\Services\QuotaService;
use App\Services\SettingsService;
use App\Services\TankService;
use App\Services\UsageService;
use Carbon\CarbonImmutable;

class SummarySheet extends BuilderSheet
{
    private const WEEKDAYS = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

    public function __construct(
        private readonly ExportContext $context,
        private readonly SettingsService $settings,
        private readonly UsageService $usage,
        private readonly QuotaService $quota,
        private readonly TankService $tanks,
    ) {}

    public function title(): string
    {
        return 'Ringkasan';
    }

    protected function build(SheetBuilder $b): void
    {
        $ctx = $this->context;
        $settings = $this->settings;
        $usage = $this->usage;
        $counters = $this->quota->counters();
        $period = $settings->resetPeriod();

        $ctx->titleBlock($b, 'Ringkasan');

        $b->section('Informasi Ekspor')
            ->header(['Keterangan', 'Nilai'])
            ->row(['Waktu ekspor', SheetBuilder::excelDate($ctx->exportedAt, $ctx->timezone)], 0, false, [1 => SheetBuilder::DATETIME])
            ->row(['Periode', $ctx->periodLabel()])
            ->row(['Diekspor oleh', $ctx->exportedBy])
            ->row(['Lokasi', $ctx->siteName ?? '-'])
            ->blank();

        $internal = $usage->total(CardCategory::Internal, $ctx->from, $ctx->to);
        $external = $usage->total(CardCategory::External, $ctx->from, $ctx->to);
        $b->section('Total Konsumsi pada Periode')
            ->header(['Kategori', 'Liter'])
            ->row(['Internal', $internal], 0, false, [1 => SheetBuilder::LITERS])
            ->row(['Eksternal', $external], 0, false, [1 => SheetBuilder::LITERS])
            ->row(['Total', $internal + $external], 0, true, [1 => SheetBuilder::LITERS])
            ->blank();

        $b->section('Kartu QR')
            ->header(['Status', 'Jumlah'])
            ->row(['Kartu Aktif', $counters['active']])
            ->row(['Kartu Lifetime', $counters['lifetime']])
            ->row(['Kartu Kedaluwarsa', $counters['expired']])
            ->row(['Kartu Habis', $counters['depleted']])
            ->blank();

        $b->section('Level Tangki Saat Ini')
            ->header(['Tangki', 'Kapasitas (L)', 'Total (%)', 'BBM (%)', 'Air (%)', 'Dibaca pada']);
        foreach (Tank::query()->orderBy('id')->get() as $tank) {
            $latest = $this->tanks->panel($tank)['latest'];
            $b->row([
                $tank->name,
                $tank->capacity_liters,
                $latest['total_pct'] ?? '-',
                $latest['fuel_pct'] ?? '-',
                $latest['water_pct'] ?? '-',
                $latest ? SheetBuilder::excelDate(CarbonImmutable::parse($latest['read_at']), $ctx->timezone) : '-',
            ], 0, false, [1 => SheetBuilder::LITERS, 2 => SheetBuilder::PERCENT, 3 => SheetBuilder::PERCENT, 4 => SheetBuilder::PERCENT, 5 => SheetBuilder::DATETIME]);
        }
        $b->blank();

        $b->section('Aturan Reset yang Berlaku')
            ->header(['Keterangan', 'Nilai'])
            ->row(['Periode reset', $period->label()])
            ->row(['Jam reset', $settings->get('reset_time')]);
        if ($period === ResetPeriod::Week) {
            $b->row(['Hari mulai minggu', self::WEEKDAYS[(int) $settings->get('week_start_day')] ?? '-']);
        }
        if ($period === ResetPeriod::Month) {
            $b->row(['Tanggal reset bulanan', (int) $settings->get('month_reset_day')]);
        }
        $b->row(['Zona waktu', $ctx->timezone])
            ->row(['Ambang kuota hampir habis', $settings->get('low_quota_percent').'% dari kuota']);

        $b->withoutAutoFilter();
    }
}
