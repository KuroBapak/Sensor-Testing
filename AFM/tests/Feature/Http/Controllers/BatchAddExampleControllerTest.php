<?php

use App\Enums\CardCategory;
use App\Enums\Validity;
use App\Imports\BatchAddImporter;
use App\Models\QrCard;
use App\Models\VehicleType;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/** The example workbook exactly as a client downloads it. */
function downloadedBatchExample(): Spreadsheet
{
    $response = test()->actingAs(admin())->get(route('batch-add.example'));
    $response->assertOk();

    return IOFactory::load($response->baseResponse->getFile()->getPathname());
}

/** The example written to disk, to feed it back to the import. */
function batchExampleFile(): string
{
    $path = tempnam(sys_get_temp_dir(), 'example').'.xlsx';
    file_put_contents($path, Excel::raw(BatchAddImporter::example(), ExcelType::XLSX));

    return $path;
}

test('guests are redirected to the login page', function () {
    $this->get(route('batch-add.example'))->assertRedirect(route('login'));
});

test('offers one example workbook with the two data sheets first and the instructions last', function () {
    $this->actingAs(admin())
        ->get(route('batch-add.example'))
        ->assertOk()
        ->assertDownload('contoh-batch-add.xlsx');

    expect(downloadedBatchExample()->getSheetNames())->toBe(['Kendaraan', 'Kartu QR', 'Petunjuk']);
});

test('the data sheets use the exact columns of the import and have several example rows', function () {
    $workbook = downloadedBatchExample();
    $vehicles = $workbook->getSheetByName('Kendaraan')->toArray();
    $cards = $workbook->getSheetByName('Kartu QR')->toArray();

    expect($vehicles[0])->toBe(['Nama Kendaraan', 'Kapasitas Maksimal (L)', 'Kuota BBM (L)'])
        ->and(count($vehicles))->toBeGreaterThan(3)
        ->and($cards[0])->toBe(['Kategori', 'Kendaraan', 'Nomor Plat', 'Masa Berlaku', 'Hari Berlaku', 'Reset (Ya/Tidak)'])
        ->and(count($cards))->toBeGreaterThan(3);
});

test('the Kartu QR sheet carries the dropdown lists', function () {
    $sheet = downloadedBatchExample()->getSheetByName('Kartu QR');

    expect($sheet->getDataValidation('A2')->getFormula1())->toBe('"Internal,Eksternal"')
        ->and($sheet->getDataValidation('D2')->getFormula1())->toBe('"Lifetime,Sementara"')
        ->and($sheet->getDataValidation('F2')->getFormula1())->toBe('"Ya,Tidak"');
});

test('the Petunjuk sheet gives numbered steps and explains every column of both sheets', function () {
    $text = collect(downloadedBatchExample()->getSheetByName('Petunjuk')->toArray())->flatten()->filter()->values();

    expect($text->first())->toBe('Cara menambah kendaraan dan kartu QR secara batch')
        ->and($text->filter(fn ($cell) => str_starts_with($cell, '1. File ini punya dua sheet')))->not->toBeEmpty()
        ->and($text)->toContain(
            'Kolom pada sheet "Kendaraan"', 'Nama Kendaraan', 'Kapasitas Maksimal (L)', 'Kuota BBM (L)',
            'Kolom pada sheet "Kartu QR"', 'Kategori', 'Nomor Plat', 'Masa Berlaku', 'Hari Berlaku', 'Reset (Ya/Tidak)', 'Jika Sementara',
            'Wajib', 'Aturan isian', 'Contoh',
        );
});

test('the Petunjuk sheet merges and wraps the steps so they stay readable', function () {
    $sheet = downloadedBatchExample()->getSheetByName('Petunjuk');

    expect($sheet->getMergeCells())->not->toBeEmpty()
        ->and($sheet->getStyle('A2')->getAlignment()->getWrapText())->toBeTrue();
});

test('importing the example works on a clean database, vehicles and cards together', function () {
    $result = app(BatchAddImporter::class)->import(batchExampleFile());

    expect($result->failed())->toBeFalse()
        ->and($result->vehicles)->toBe(6)
        ->and($result->cards)->toBe(5)
        ->and(VehicleType::where('name', 'Hitachi ZX200 Excavator')->sole()->quota_liters)->toBe(250.0);

    $temporary = QrCard::where('plate_number', 'KT 2201 CD')->sole();
    $pool = QrCard::where('plate_number', 'KT 4401 GH')->sole();
    expect($temporary->category)->toBe(CardCategory::External)
        ->and($temporary->validity)->toBe(Validity::Temporary)
        ->and($temporary->valid_days)->toBe(30)
        ->and($temporary->reset_enabled)->toBeTrue()
        ->and($pool->validity)->toBe(Validity::Lifetime)
        ->and($pool->reset_enabled)->toBeFalse();
});

test('uploading the downloaded example through the page works too', function () {
    $this->actingAs(admin())
        ->post(route('batch-add.store'), ['file' => new UploadedFile(batchExampleFile(), 'contoh-batch-add.xlsx', null, null, true)])
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.message', '6 kendaraan dan 5 kartu QR berhasil ditambahkan.');
});

test('importing the same example twice shows the duplicates and adds nothing more', function () {
    app(BatchAddImporter::class)->import(batchExampleFile());

    $second = app(BatchAddImporter::class)->import(batchExampleFile());

    expect($second->failed())->toBeTrue()
        ->and($second->errors[0]['sheet'])->toBe('Kendaraan')
        ->and($second->errors[0]['message'])->toBe("Baris 2: Kendaraan 'Suzuki Carry' sudah terdaftar.")
        ->and(VehicleType::count())->toBe(6)
        ->and(QrCard::count())->toBe(5);
});
