<?php

use App\Exports\ExportContext;
use App\Exports\FullExport;
use App\Models\ActivityLog;
use App\Models\FuelTransaction;
use App\Models\QrCard;
use App\Models\QuotaAdjustment;
use App\Models\Tank;
use App\Models\TankReading;
use App\Models\User;
use App\Models\VehicleType;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

const SHEET_NAMES = [
    'Ringkasan', 'Kendaraan', 'Kartu QR', 'Pemakaian Internal', 'Pemakaian Eksternal',
    'Riwayat Pengisian', 'Riwayat Top-up', 'Level Tangki 1', 'Level Tangki 2',
];

/** A small site: two vehicle types, four cards in both categories, fuel, top-ups and readings. */
function seedSite(): array
{
    $granMax = VehicleType::factory()->quota(20)->create(['name' => 'Gran Max']);
    $hilux = VehicleType::factory()->quota(40)->create(['name' => 'Hilux']);

    $a = QrCard::factory()->for($granMax)->create(['plate_number' => 'B 1 AAA']);
    $b = QrCard::factory()->for($granMax)->create(['plate_number' => 'B 2 BBB']);
    $c = QrCard::factory()->for($hilux)->create(['plate_number' => 'B 3 CCC']);
    $e = QrCard::factory()->external()->for($granMax)->create(['plate_number' => 'B 4 EXT']);

    FuelTransaction::factory()->for($a)->create(['liters' => 5.5, 'dispensed_at' => '2026-10-22 08:00:00']);
    FuelTransaction::factory()->for($a)->create(['liters' => 4.5, 'dispensed_at' => '2026-10-21 08:00:00']);
    FuelTransaction::factory()->for($b)->create(['liters' => 3, 'dispensed_at' => '2026-10-22 09:00:00']);
    FuelTransaction::factory()->for($c)->create(['liters' => 11, 'dispensed_at' => '2026-10-22 10:00:00']);
    FuelTransaction::factory()->for($e)->create(['liters' => 9, 'dispensed_at' => '2026-10-22 11:00:00']);
    FuelTransaction::factory()->for($a)->create(['liters' => 99, 'dispensed_at' => '2026-09-01 08:00:00']); // before the period
    QuotaAdjustment::factory()->for($a)->create(['liters' => 6, 'note' => 'Tambahan', 'user_id' => User::factory()->create(['name' => 'Siti'])->id, 'created_at' => '2026-10-22 12:00:00']);

    $tank1 = Tank::factory()->create(['name' => 'Tangki 1', 'capacity_liters' => 2300]);
    $tank2 = Tank::factory()->create(['name' => 'Tangki 2', 'capacity_liters' => 2800]);
    foreach ([12, 13, 14] as $hour) {
        TankReading::factory()->for($tank1)->create(['read_at' => sprintf('2026-10-22 %02d:00:00', $hour), 'total_pct' => 80, 'fuel_pct' => 60, 'water_pct' => 20]);
    }
    TankReading::factory()->for($tank2)->create(['read_at' => '2026-10-22 14:00:00', 'total_pct' => 50, 'fuel_pct' => 48, 'water_pct' => 2]);
    TankReading::factory()->for($tank1)->create(['read_at' => '2026-09-01 10:00:00', 'total_pct' => 70, 'fuel_pct' => 68, 'water_pct' => 2]); // before the period

    return compact('a', 'b', 'c', 'e');
}

function exportContext(string $from = '2026-10-01', string $to = '2026-10-22'): ExportContext
{
    return ExportContext::forDates($from, $to, 'Siti', 'Asia/Jakarta', 'Site Utara');
}

/** Write the workbook the way a download would, and read it back like Excel does. */
function exportedWorkbook(?ExportContext $context = null): Spreadsheet
{
    $raw = Excel::raw(app()->make(FullExport::class, ['context' => $context ?? exportContext()]), ExcelType::XLSX);
    $path = tempnam(sys_get_temp_dir(), 'export').'.xlsx';
    file_put_contents($path, $raw);

    return IOFactory::load($path);
}

beforeEach(function () {
    $this->travelTo('2026-10-22 14:30:00');
});

test('the workbook has the nine tabs in order', function () {
    seedSite();

    expect(exportedWorkbook()->getSheetNames())->toBe(SHEET_NAMES);
});

test('every tab starts with a title block, a blank row and a header row', function () {
    seedSite();
    $workbook = exportedWorkbook();

    foreach (SHEET_NAMES as $name) {
        $sheet = $workbook->getSheetByName($name);

        expect($sheet->getCell('A1')->getValue())->toContain(' — Site Utara')
            ->and($sheet->getCell('A2')->getValue())->toBe('Periode: 01/10/2026 – 22/10/2026')
            ->and($sheet->getCell('A3')->getValue())->toBe('Diekspor: 22/10/2026 14:30 oleh Siti')
            ->and($sheet->getCell('A4')->getValue())->toBeNull();
    }
});

test('every tab has a bold filled header, a frozen pane, an auto-filter and print settings', function () {
    seedSite();
    $workbook = exportedWorkbook();

    foreach (SHEET_NAMES as $name) {
        $sheet = $workbook->getSheetByName($name);
        $headerRow = (int) preg_replace('/\D/', '', $sheet->getFreezePane() ?? '') - 1;
        $header = $sheet->getStyle("A{$headerRow}");

        expect($sheet->getFreezePane())->not->toBeNull()
            ->and($header->getFont()->getBold())->toBeTrue()
            ->and($header->getFill()->getStartColor()->getRGB())->toBe('1F4E79')
            ->and($sheet->getPageSetup()->getOrientation())->toBe(PageSetup::ORIENTATION_LANDSCAPE)
            ->and($sheet->getPageSetup()->getFitToWidth())->toBe(1)
            ->and($sheet->getPageSetup()->getRowsToRepeatAtTop())->toEqual([$headerRow, $headerRow]);

        if ($name !== 'Ringkasan') {
            expect($sheet->getAutoFilter()->getRange())->not->toBe('');
        }
    }
});

test('the vehicle tab lists every type with its quota and card count', function () {
    seedSite();
    $sheet = exportedWorkbook()->getSheetByName('Kendaraan');

    expect($sheet->rangeToArray('A5:D7', null, true, false))->toEqual([
        ['Kendaraan', 'Kapasitas Maksimal (L)', 'Kuota BBM (L / hari)', 'Jumlah Kartu QR'],
        ['Gran Max', 100, 20, 3],
        ['Hilux', 100, 40, 1],
    ]);
});

test('the usage tabs group liters by vehicle type with subtotals and match the database totals', function () {
    seedSite();
    $workbook = exportedWorkbook();

    $internal = $workbook->getSheetByName('Pemakaian Internal')->rangeToArray('A5:D11', null, true, false);
    $external = $workbook->getSheetByName('Pemakaian Eksternal')->rangeToArray('A5:D8', null, true, false);

    expect($internal)->toEqual([
        ['Nomor Plat', 'Kendaraan', 'Pemakaian (L)', 'Keterangan'],
        ['B 1 AAA', 'Gran Max', 10, null],          // 5.5 + 4.5; the 99 L in September is outside the period
        ['B 2 BBB', 'Gran Max', 3, null],
        ['Subtotal Gran Max', null, 13, null],
        ['B 3 CCC', 'Hilux', 11, null],
        ['Subtotal Hilux', null, 11, null],
        ['Total Internal', null, 24, null],
    ])->and($external)->toEqual([
        ['Nomor Plat', 'Kendaraan', 'Pemakaian (L)', 'Keterangan'],
        ['B 4 EXT', 'Gran Max', 9, null],
        ['Subtotal Gran Max', null, 9, null],
        ['Total Eksternal', null, 9, null],
    ]);

    $database = FuelTransaction::where('dispensed_at', '>=', '2026-10-01')->sum('liters');
    expect($database)->toEqual(33);                  // 24 internal + 9 external
});

test('the usage tabs count only fuel in the chosen period', function () {
    seedSite();

    $sheet = exportedWorkbook(exportContext('2026-10-22', '2026-10-22'))->getSheetByName('Pemakaian Internal');

    expect($sheet->getCell('C6')->getValue())->toEqual(5.5)      // B 1 AAA, only today's fueling
        ->and($sheet->getCell('C11')->getValue())->toEqual(19.5);  // total internal today
});

test('usage tabs use outline levels so groups collapse and subtotals stay visible', function () {
    seedSite();
    $sheet = exportedWorkbook()->getSheetByName('Pemakaian Internal');

    expect($sheet->getRowDimension(6)->getOutlineLevel())->toBe(1)
        ->and($sheet->getRowDimension(8)->getOutlineLevel())->toBe(0)
        ->and($sheet->getStyle('A8')->getFont()->getBold())->toBeTrue()
        ->and($sheet->getStyle('A11')->getFont()->getBold())->toBeTrue();
});

test('the card tab groups cards by category and vehicle type', function () {
    seedSite();
    $sheet = exportedWorkbook()->getSheetByName('Kartu QR');
    $labels = array_column($sheet->rangeToArray('A6:A20'), 0);

    expect($labels)->toContain('B 1 AAA', 'Subtotal Gran Max (2 kartu)', 'Total Internal (3 kartu)', 'Total Eksternal (1 kartu)');
    expect($sheet->getRowDimension(6)->getOutlineLevel())->toBe(2);
});

test('the fueling history is grouped by date and limited to the period', function () {
    seedSite();
    $sheet = exportedWorkbook()->getSheetByName('Riwayat Pengisian');
    $first = array_column($sheet->rangeToArray('A6:A20'), 0);

    expect($first)->toContain('Total 21/10/2026', 'Total 22/10/2026', 'Total Periode')
        ->and(array_filter($first, fn ($v) => $v === 'Total 01/09/2026'))->toBeEmpty();
});

test('the top-up history lists who topped up, how many liters and the note', function () {
    seedSite();
    $sheet = exportedWorkbook()->getSheetByName('Riwayat Top-up');

    expect($sheet->rangeToArray('B6:E6', null, true, false)[0])->toEqual(['B 1 AAA', 6, 'Tambahan', 'Siti']);
});

test('the tank tabs list the hourly readings of the period for their tank', function () {
    seedSite();
    $workbook = exportedWorkbook();

    $tank1 = $workbook->getSheetByName('Level Tangki 1')->rangeToArray('B6:D8', null, true, false);
    $tank2 = $workbook->getSheetByName('Level Tangki 2')->rangeToArray('B6:D7', null, true, false);

    expect($tank1)->toEqual([[80, 60, 20], [80, 60, 20], [80, 60, 20]])
        ->and($tank2[0])->toEqual([50, 48, 2])
        ->and($tank2[1])->toEqual([null, null, null]);
});

test('the summary tab shows totals, counters, tank levels and the reset rules in force', function () {
    seedSite();
    setSettings(['reset_period' => 'week', 'week_start_day' => 3]);
    $sheet = exportedWorkbook()->getSheetByName('Ringkasan');
    $text = collect($sheet->toArray())->flatten()->filter()->values();

    expect($text)->toContain('Total Konsumsi pada Periode', 'Kartu Aktif', 'Level Tangki Saat Ini', 'Aturan Reset yang Berlaku', 'Minggu', 'Rabu', 'Asia/Jakarta');
});

test('numbers and dates are real cells with the standard formats', function () {
    seedSite();
    $workbook = exportedWorkbook();

    $fueling = $workbook->getSheetByName('Riwayat Pengisian');
    $tank = $workbook->getSheetByName('Level Tangki 1');
    $usage = $workbook->getSheetByName('Pemakaian Internal');

    expect($fueling->getCell('E6')->getDataType())->toBe('n')
        ->and($fueling->getStyle('E6')->getNumberFormat()->getFormatCode())->toBe('#,##0.0')
        ->and($fueling->getCell('A6')->getDataType())->toBe('n')
        ->and($fueling->getStyle('A6')->getNumberFormat()->getFormatCode())->toBe('dd/mm/yyyy hh:mm')
        ->and($tank->getStyle('B6')->getNumberFormat()->getFormatCode())->toBe('0.0')
        ->and($usage->getCell('C6')->getDataType())->toBe('n');
});

test('an empty site still produces all nine tabs without failing', function () {
    $workbook = exportedWorkbook();

    expect($workbook->getSheetNames())->toBe(['Ringkasan', 'Kendaraan', 'Kartu QR', 'Pemakaian Internal', 'Pemakaian Eksternal', 'Riwayat Pengisian', 'Riwayat Top-up']);
});

test('deleted cards stay in the usage and history tabs', function () {
    $cards = seedSite();
    $cards['b']->delete();
    $workbook = exportedWorkbook();

    $usage = collect($workbook->getSheetByName('Pemakaian Internal')->toArray())->flatten();
    $cardsTab = collect($workbook->getSheetByName('Kartu QR')->toArray())->flatten();

    expect($usage)->toContain('B 2 BBB', 'Kartu dihapus')
        ->and($cardsTab)->not->toContain('B 2 BBB');
});

test('the download is named with the export time and logged', function () {
    seedSite();
    Excel::fake();

    $this->actingAs(admin())->get(route('export.full', ['from' => '2026-10-01', 'to' => '2026-10-22']))->assertOk();

    Excel::assertDownloaded('fuel-export-20261022-1430.xlsx', fn (FullExport $export) => count($export->sheets()) === 9);
    $this->assertDatabaseHas('activity_logs', ['action' => 'export.full']);
});

test('the period defaults to the current month', function () {
    seedSite();
    Excel::fake();

    $this->actingAs(admin())->get(route('export.full'))->assertOk();

    Excel::assertDownloaded('fuel-export-20261022-1430.xlsx');
    expect(ActivityLog::sole()->details)->toBe(['from' => '2026-10-01', 'to' => '2026-10-22']);
});

test('rejects an end date before the start date', function () {
    $this->actingAs(admin())
        ->get(route('export.full', ['from' => '2026-10-22', 'to' => '2026-10-01']))
        ->assertSessionHasErrors('to');
});

test('guests are redirected to the login page', function () {
    $this->get(route('export.full'))->assertRedirect(route('login'));
});

test('both roles can export everything', function (string $role) {
    Excel::fake();

    $this->actingAs(User::factory()->{$role}()->create())->get(route('export.full'))->assertOk();
})->with(['admin', 'superAdmin']);
