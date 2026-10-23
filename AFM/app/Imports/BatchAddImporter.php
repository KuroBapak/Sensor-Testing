<?php

namespace App\Imports;

use App\Exports\ImportExample;
use App\Exports\InstructionsSheet;
use App\Exports\TemplateExport;
use App\Models\User;
use App\Models\VehicleType;
use App\Services\CardService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Batch Add: one workbook adds vehicle types ("Kendaraan" sheet) and QR cards
 * ("Kartu QR" sheet) together, because the cards depend on the types. Either
 * sheet may be left out. Every row of both sheets is checked first and any error
 * adds nothing, so a half-imported fleet cannot happen.
 */
class BatchAddImporter
{
    public function __construct(
        private readonly SpreadsheetReader $reader,
        private readonly VehicleTypeSheet $vehicleSheet,
        private readonly QrCardSheet $cardSheet,
        private readonly CardService $cards,
    ) {}

    public function import(UploadedFile|string $file, ?User $user = null): ImportResult
    {
        $opened = $this->reader->open($file);
        if ($opened['errors'] !== []) {
            return new ImportResult(errors: $opened['errors']);
        }

        $vehicleLines = $opened['sheets'][SpreadsheetReader::sheetKey(VehicleTypeSheet::NAME)] ?? null;
        $cardLines = $opened['sheets'][SpreadsheetReader::sheetKey(QrCardSheet::NAME)] ?? null;

        if ($vehicleLines === null && $cardLines === null) {
            return new ImportResult(errors: [SpreadsheetReader::error('-', 1, '-', __('fuel.import.no_sheets'))]);
        }

        $vehicleTable = $this->reader->table(VehicleTypeSheet::NAME, $vehicleLines ?? [], VehicleTypeSheet::COLUMNS);
        $cardTable = $this->reader->table(QrCardSheet::NAME, $cardLines ?? [], QrCardSheet::COLUMNS);

        // A sheet that is absent is simply not imported; a present sheet must have its columns.
        $errors = [
            ...($vehicleLines === null ? [] : $vehicleTable['errors']),
            ...($cardLines === null ? [] : $cardTable['errors']),
        ];
        if ($errors !== []) {
            return new ImportResult(errors: $errors);
        }

        $rowCount = count($vehicleTable['rows']) + count($cardTable['rows']);
        if ($rowCount > SpreadsheetReader::MAX_ROWS) {
            return new ImportResult(errors: [SpreadsheetReader::error('-', 1, '-', __('fuel.import.too_many', [
                'max' => number_format(SpreadsheetReader::MAX_ROWS, 0, ',', '.'),
                'count' => number_format($rowCount, 0, ',', '.'),
            ]))]);
        }

        $typesInFile = [];
        foreach ($vehicleTable['rows'] as ['cells' => $cells]) {
            if ($cells['name'] !== '') {
                $typesInFile[mb_strtolower($cells['name'])] = true;
            }
        }

        $vehicles = $this->vehicleSheet->check($vehicleTable['rows']);
        $cards = $this->cardSheet->check($cardTable['rows'], $typesInFile);

        if ($vehicles['errors'] !== [] || $cards['errors'] !== []) {
            return new ImportResult(errors: [...$vehicles['errors'], ...$cards['errors']]);
        }

        DB::transaction(function () use ($vehicles, $cards, $user) {
            foreach ($vehicles['valid'] as $attributes) {
                VehicleType::query()->create($attributes);
            }

            $typeIds = VehicleType::query()->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [mb_strtolower($name) => $id])->all();

            foreach ($cards['valid'] as $attributes) {
                $attributes['vehicle_type_id'] = $typeIds[$attributes['vehicle']];
                unset($attributes['vehicle']);

                $this->cards->create($attributes, $user, log: false);
            }
        });

        return new ImportResult(count($vehicles['valid']), count($cards['valid']));
    }

    /**
     * "Contoh Batch Add": the Kendaraan and Kartu QR sheets filled in, so it imports
     * as it stands on a clean database, plus a Petunjuk sheet explaining every column.
     */
    public static function example(): ImportExample
    {
        return new ImportExample(
            new TemplateExport(VehicleTypeSheet::NAME, VehicleTypeSheet::HEADINGS, [
                ['Suzuki Carry', 40, 15],
                ['Mitsubishi Colt Diesel', 100, 45],
                ['Toyota Dyna', 100, 50],
                ['Hino Ranger', 200, 90],
                ['Volvo FM Dump Truck', 400, 180],
                ['Hitachi ZX200 Excavator', 400, 250],
            ]),
            new TemplateExport(QrCardSheet::NAME, QrCardSheet::HEADINGS, [
                ['Internal', 'Suzuki Carry', 'KT 1101 AA', 'Lifetime', null, 'Ya'],
                ['Internal', 'Suzuki Carry', 'KT 1102 AB', 'Lifetime', null, 'Ya'],
                ['Internal', 'Hitachi ZX200 Excavator', 'KT 4401 GH', 'Lifetime', null, 'Tidak'],
                ['Eksternal', 'Toyota Dyna', 'KT 2201 CD', 'Sementara', 30, 'Ya'],
                ['Eksternal', 'Hino Ranger', 'KT 3301 EF', 'Sementara', 90, 'Tidak'],
            ], QrCardSheet::DROPDOWNS),
            new InstructionsSheet(
                'Petunjuk',
                'Cara menambah kendaraan dan kartu QR secara batch',
                [
                    'File ini punya dua sheet isian: "Kendaraan" (jenis atau model kendaraan beserta kuotanya) dan "Kartu QR" (satu baris per mobil). Keduanya diunggah sekaligus dalam satu file, karena kartu bergantung pada kendaraannya.',
                    'Hapus baris contoh di kedua sheet, lalu isi data Anda mulai baris 2. Baris 1 adalah judul kolom: jangan diubah, dihapus, atau dipindah. Sheet yang tidak diperlukan boleh dikosongkan atau dihapus.',
                    'Kolom "Kendaraan" pada sheet "Kartu QR" boleh berisi kendaraan yang sudah terdaftar di aplikasi atau kendaraan baru yang Anda tulis di sheet "Kendaraan" pada file yang sama.',
                    'Simpan sebagai .xlsx (maksimal 2 MB dan 1.000 baris data, kedua sheet dihitung bersama).',
                    'Di aplikasi klik Batch Add (di halaman Kendaraan atau Generator Kode QR), pilih file, lalu klik Impor. Kendaraan ditambahkan lebih dulu, lalu kartunya. Kode QR dibuat otomatis dan dapat dilihat lewat tombol Tampilkan QR.',
                    'Seluruh file diperiksa dulu. Jika satu baris di sheet mana pun salah, tidak ada yang ditambahkan dan aplikasi menampilkan nama sheet, nomor baris, dan alasannya. Perbaiki di Excel lalu unggah ulang.',
                ],
                [
                    [
                        'title' => 'Kolom pada sheet "Kendaraan"',
                        'columns' => [
                            ['Nama Kendaraan', 'Ya', 'Teks, maksimal 100 karakter. Tidak boleh sama dengan kendaraan yang sudah terdaftar (huruf besar/kecil diabaikan) atau dengan baris lain di sheet ini.', 'Suzuki Carry'],
                            ['Kapasitas Maksimal (L)', 'Ya', 'Angka lebih dari 0: kapasitas tangki kendaraan dalam liter.', '40'],
                            ['Kuota BBM (L)', 'Ya', 'Angka lebih dari 0: liter yang boleh diambil per periode reset (hari, minggu, atau bulan sesuai Pengaturan). Berlaku untuk semua kartu QR dengan kendaraan ini.', '15'],
                        ],
                    ],
                    [
                        'title' => 'Kolom pada sheet "Kartu QR"',
                        'columns' => [
                            ['Kategori', 'Ya', 'Pilih Internal (armada sendiri) atau Eksternal (sewa).', 'Internal'],
                            ['Kendaraan', 'Ya', 'Nama kendaraan persis seperti di aplikasi atau di sheet "Kendaraan" (huruf besar/kecil diabaikan). Kuota kartu mengikuti kendaraan ini.', 'Suzuki Carry'],
                            ['Nomor Plat', 'Ya', 'Maksimal 20 karakter. Harus unik di antara kartu yang belum kedaluwarsa atau dihapus, dan tidak boleh ganda di sheet ini. Huruf dijadikan kapital dan spasi dirapikan.', 'KT 1101 AA'],
                            ['Masa Berlaku', 'Ya', 'Lifetime (tidak pernah kedaluwarsa) atau Sementara.', 'Sementara'],
                            ['Hari Berlaku', 'Jika Sementara', 'Bilangan bulat 1 sampai 3650. Hari pembuatan dihitung hari ke-1 dan kartu berakhir pukul 23:59 pada hari terakhir. Kosongkan untuk Lifetime.', '30'],
                            ['Reset (Ya/Tidak)', 'Ya', 'Ya: kuota terisi penuh lagi setiap periode reset. Tidak: kuota menjadi satu kantong untuk seluruh masa berlaku dan baru bertambah lewat top-up.', 'Ya'],
                        ],
                    ],
                ],
            ),
        );
    }
}
