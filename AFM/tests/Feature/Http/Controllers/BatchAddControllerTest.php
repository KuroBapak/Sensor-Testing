<?php

use App\Enums\CardCategory;
use App\Enums\Validity;
use App\Models\ActivityLog;
use App\Models\QrCard;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;

const BATCH_VEHICLE_HEADER = ['Nama Kendaraan', 'Kapasitas Maksimal (L)', 'Kuota BBM (L)'];

const BATCH_CARD_HEADER = ['Kategori', 'Kendaraan', 'Nomor Plat', 'Masa Berlaku', 'Hari Berlaku', 'Reset (Ya/Tidak)'];

/**
 * Post a Batch Add workbook. Either argument may be null to leave that sheet out.
 *
 * @param  list<list<mixed>>|null  $vehicles  data rows of the Kendaraan sheet
 * @param  list<list<mixed>>|null  $cards  data rows of the Kartu QR sheet
 */
function batchAdd(?array $vehicles, ?array $cards, ?User $user = null): TestResponse
{
    $sheets = [];
    if ($vehicles !== null) {
        $sheets['Kendaraan'] = [BATCH_VEHICLE_HEADER, ...$vehicles];
    }
    if ($cards !== null) {
        $sheets['Kartu QR'] = [BATCH_CARD_HEADER, ...$cards];
    }

    return test()->actingAs($user ?? admin())->post(route('batch-add.store'), ['file' => workbookUpload($sheets)]);
}

beforeEach(function () {
    // Thursday 22 Oct 2026, 14:30 WIB.
    $this->travelTo('2026-10-22 14:30:00');
});

test('guests are redirected to the login page', function () {
    $this->post(route('batch-add.store'), ['file' => workbookUpload(['Kendaraan' => [BATCH_VEHICLE_HEADER]])])
        ->assertRedirect(route('login'));
});

test('both roles can batch add', function (string $role) {
    batchAdd([['Daihatsu Gran Max', 45, 20]], null, User::factory()->{$role}()->create())->assertSessionHasNoErrors();

    expect(VehicleType::count())->toBe(1);
})->with(['admin', 'superAdmin']);

test('adds vehicle types alone', function () {
    batchAdd([['Daihatsu Gran Max', 45, 20], ['Toyota Hilux', 80, 40]], null)
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.message', '2 kendaraan berhasil ditambahkan.');

    expect(VehicleType::orderBy('name')->pluck('quota_liters', 'name')->all())
        ->toBe(['Daihatsu Gran Max' => 20.0, 'Toyota Hilux' => 40.0]);
});

test('adds QR cards alone for vehicles that exist already', function () {
    VehicleType::factory()->create(['name' => 'Daihatsu Gran Max']);

    batchAdd(null, [['Internal', 'daihatsu gran max', 'kt 1111 aa', 'Lifetime', null, 'Ya']])
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.message', '1 kartu QR berhasil ditambahkan.');

    expect(QrCard::sole()->plate_number)->toBe('KT 1111 AA');
});

test('adds vehicle types and the QR cards that use them in one go', function () {
    $user = admin();
    VehicleType::factory()->create(['name' => 'Toyota Hilux']);

    batchAdd(
        [['Daihatsu Gran Max', 45, 20], ['Isuzu Elf', 100, 50]],
        [
            ['Internal', 'Daihatsu Gran Max', 'KT 1111 AA', 'Sementara', 30, 'Ya'],
            ['Eksternal', 'isuzu elf', 'KT 2222 BB', 'Lifetime', null, 'Tidak'],
            ['Internal', 'Toyota Hilux', 'KT 3333 CC', 'Lifetime', null, 'Ya'],
        ],
        $user,
    )
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.message', '2 kendaraan dan 3 kartu QR berhasil ditambahkan.');

    $temporary = QrCard::where('plate_number', 'KT 1111 AA')->sole();
    $pool = QrCard::where('plate_number', 'KT 2222 BB')->sole();

    expect(VehicleType::count())->toBe(3)
        ->and($temporary->vehicleType->name)->toBe('Daihatsu Gran Max')
        ->and($temporary->category)->toBe(CardCategory::Internal)
        ->and($temporary->validity)->toBe(Validity::Temporary)
        ->and($temporary->valid_days)->toBe(30)
        ->and($temporary->expires_at->toDateString())->toBe('2026-11-20')
        ->and($temporary->created_by)->toBe($user->id)
        ->and($pool->vehicleType->name)->toBe('Isuzu Elf')
        ->and($pool->category)->toBe(CardCategory::External)
        ->and($pool->reset_enabled)->toBeFalse()
        ->and($pool->expires_at)->toBeNull();
});

test('logs the batch once instead of once per row', function () {
    batchAdd(
        [['Daihatsu Gran Max', 45, 20]],
        [['Internal', 'Daihatsu Gran Max', 'KT 1111 AA', 'Lifetime', null, 'Ya'], ['Internal', 'Daihatsu Gran Max', 'KT 2222 BB', 'Lifetime', null, 'Ya']],
    );

    expect(ActivityLog::pluck('action')->all())->toBe(['import.batch_add'])
        ->and(ActivityLog::sole()->details)->toBe(['vehicle_types' => 1, 'qr_cards' => 2]);
});

test('reads columns by header and sheets by name, so reordering and letter case do not matter', function () {
    $file = workbookUpload([
        'kartu qr' => [
            ['Reset (Ya/Tidak)', 'Nomor Plat', 'Kendaraan', 'Kategori', 'Masa Berlaku', 'Hari Berlaku'],
            ['Ya', 'KT 1111 AA', 'Daihatsu Gran Max', 'Internal', 'Lifetime', null],
        ],
        'KENDARAAN' => [
            ['Kuota BBM (L)', 'Nama Kendaraan', 'Kapasitas Maksimal (L)'],
            [20, 'Daihatsu Gran Max', 45],
        ],
    ]);

    $this->actingAs(admin())->post(route('batch-add.store'), ['file' => $file])->assertSessionHasNoErrors();

    expect(VehicleType::sole()->only(['name', 'max_capacity_liters', 'quota_liters']))
        ->toBe(['name' => 'Daihatsu Gran Max', 'max_capacity_liters' => 45.0, 'quota_liters' => 20.0])
        ->and(QrCard::count())->toBe(1);
});

test('skips blank spreadsheet rows', function () {
    batchAdd([['Daihatsu Gran Max', 45, 20], [null, null, null], ['Toyota Hilux', 80, 40]], [])
        ->assertSessionHasNoErrors();

    expect(VehicleType::count())->toBe(2);
});

test('adds nothing when a card is wrong even though the vehicles are fine', function () {
    batchAdd(
        [['Daihatsu Gran Max', 45, 20]],
        [['Internal', 'Daihatsu Gran Max', 'KT 1111 AA', 'Lifetime', null, 'Mungkin']],
    )
        ->assertSessionHasErrors(['file' => 'Impor dibatalkan: 1 kesalahan ditemukan. Tidak ada data yang ditambahkan.'])
        ->assertInertiaFlash('import_errors.0.sheet', 'Kartu QR');

    expect(VehicleType::count())->toBe(0)
        ->and(QrCard::count())->toBe(0);
});

test('adds nothing when a vehicle is wrong even though the cards are fine', function () {
    VehicleType::factory()->create(['name' => 'Toyota Hilux']);

    batchAdd(
        [['Isuzu Elf', 0, 50]],
        [['Internal', 'Toyota Hilux', 'KT 1111 AA', 'Lifetime', null, 'Ya']],
    )->assertSessionHasErrors('file');

    expect(VehicleType::count())->toBe(1)
        ->and(QrCard::count())->toBe(0);
});

test('lists every vehicle problem with its sheet and row number', function () {
    VehicleType::factory()->create(['name' => 'Daihatsu Gran Max']);

    batchAdd([
        ['Toyota Hilux', 80, 40],            // row 2, fine
        ['Daihatsu Gran Max', 45, 20],       // row 3, exists already
        ['', 45, 20],                        // row 4, no name
        ['Isuzu Elf', 0, 50],                // row 5, capacity not positive
        ['Hino Dutro', 100, 'banyak'],       // row 6, quota not a number
        ['toyota hilux', 80, 40],            // row 7, same as row 2
    ], null)
        ->assertSessionHasErrors(['file' => 'Impor dibatalkan: 5 kesalahan ditemukan. Tidak ada data yang ditambahkan.'])
        ->assertInertiaFlash('import_errors.0.sheet', 'Kendaraan')
        ->assertInertiaFlash('import_errors.0.row', 3)
        ->assertInertiaFlash('import_errors.0.column', 'Nama Kendaraan')
        ->assertInertiaFlash('import_errors.0.message', "Baris 3: Kendaraan 'Daihatsu Gran Max' sudah terdaftar.")
        ->assertInertiaFlash('import_errors.1.message', 'Baris 4: Nama Kendaraan wajib diisi.')
        ->assertInertiaFlash('import_errors.2.message', 'Baris 5: Kapasitas Maksimal (L) harus berupa angka lebih dari 0.')
        ->assertInertiaFlash('import_errors.3.message', 'Baris 6: Kuota BBM (L) harus berupa angka lebih dari 0.')
        ->assertInertiaFlash('import_errors.4.message', "Baris 7: 'toyota hilux' muncul lebih dari satu kali (juga di baris 2).");

    expect(VehicleType::pluck('name')->all())->toBe(['Daihatsu Gran Max']);
});

test('lists every card problem with its sheet and row number', function () {
    VehicleType::factory()->create(['name' => 'Daihatsu Gran Max']);
    QrCard::factory()->create(['plate_number' => 'KT 9999 ZZ']);

    batchAdd(null, [
        ['Internal', 'Daihatsu Gran Max', 'KT 1111 AA', 'Lifetime', null, 'Ya'],      // row 2, fine
        ['Rental', 'Daihatsu Gran Max', 'KT 3333 CC', 'Lifetime', null, 'Ya'],        // row 3, unknown category
        ['Internal', 'Tidak Ada', 'KT 4444 DD', 'Lifetime', null, 'Ya'],              // row 4, unknown vehicle
        ['Internal', 'Daihatsu Gran Max', 'KT 9999 ZZ', 'Lifetime', null, 'Ya'],      // row 5, plate in use
        ['Internal', 'Daihatsu Gran Max', 'kt 1111 aa', 'Lifetime', null, 'Ya'],      // row 6, plate repeated in file
        ['Internal', 'Daihatsu Gran Max', 'KT 5555 EE', 'Sementara', null, 'Ya'],     // row 7, days missing
        ['Internal', 'Daihatsu Gran Max', 'KT 6666 FF', 'Seumur hidup', null, 'Ya'],  // row 8, unknown validity
        ['Internal', 'Daihatsu Gran Max', 'KT 7777 GG', 'Lifetime', null, 'Mungkin'], // row 9, reset not Ya/Tidak
        ['', 'Daihatsu Gran Max', '', 'Lifetime', null, ''],                          // row 10, fields missing
    ])
        ->assertSessionHasErrors('file')
        ->assertInertiaFlash('import_errors.0.sheet', 'Kartu QR')
        ->assertInertiaFlash('import_errors.0.message', "Baris 3: Kategori 'Rental' tidak dikenal. Gunakan salah satu dari: Internal, Eksternal.")
        ->assertInertiaFlash('import_errors.1.message', "Baris 4: Kendaraan 'Tidak Ada' tidak ditemukan.")
        ->assertInertiaFlash('import_errors.2.message', "Baris 5: Nomor plat 'KT 9999 ZZ' sudah dipakai oleh kartu yang masih berlaku.")
        ->assertInertiaFlash('import_errors.3.message', "Baris 6: 'KT 1111 AA' muncul lebih dari satu kali (juga di baris 2).")
        ->assertInertiaFlash('import_errors.4.message', 'Baris 7: Hari Berlaku harus berupa bilangan bulat 1 sampai 3650.')
        ->assertInertiaFlash('import_errors.5.message', "Baris 8: Masa Berlaku 'Seumur hidup' tidak dikenal. Gunakan salah satu dari: Lifetime, Sementara.")
        ->assertInertiaFlash('import_errors.6.message', "Baris 9: Reset (Ya/Tidak) 'Mungkin' tidak dikenal. Gunakan salah satu dari: Ya, Tidak.")
        ->assertInertiaFlash('import_errors.7.message', 'Baris 10: Kategori wajib diisi.');

    expect(QrCard::count())->toBe(1);
});

test('reports vehicle errors before card errors, each with its own sheet', function () {
    batchAdd(
        [['', 45, 20]],
        [['Internal', 'Tidak Ada', 'KT 1111 AA', 'Lifetime', null, 'Ya']],
    )
        ->assertInertiaFlash('import_errors.0.sheet', 'Kendaraan')
        ->assertInertiaFlash('import_errors.1.sheet', 'Kartu QR');
});

test('does not pile a second error on a card whose vehicle row is the problem', function () {
    batchAdd(
        [['Isuzu Elf', 0, 50]],
        [['Internal', 'Isuzu Elf', 'KT 1111 AA', 'Lifetime', null, 'Ya']],
    )
        ->assertSessionHasErrors(['file' => 'Impor dibatalkan: 1 kesalahan ditemukan. Tidak ada data yang ditambahkan.']);
});

test('lets a plate be reissued once the old card has expired', function () {
    VehicleType::factory()->create(['name' => 'Daihatsu Gran Max']);
    QrCard::factory()->expired()->create(['plate_number' => 'KT 1111 AA']);

    batchAdd(null, [['Internal', 'Daihatsu Gran Max', 'KT 1111 AA', 'Lifetime', null, 'Ya']])->assertSessionHasNoErrors();

    expect(QrCard::where('plate_number', 'KT 1111 AA')->count())->toBe(2);
});

test('rejects a workbook that has neither sheet', function () {
    $file = workbookUpload(['Sheet1' => [['a', 'b'], [1, 2]]]);

    $this->actingAs(admin())->post(route('batch-add.store'), ['file' => $file])
        ->assertSessionHasErrors('file')
        ->assertInertiaFlash('import_errors.0.message', "Baris 1: Sheet 'Kendaraan' atau 'Kartu QR' tidak ditemukan. Gunakan file Contoh Batch Add sebagai acuan.");
});

test('rejects a sheet that lacks a required column and names the sheet', function () {
    $file = workbookUpload([
        'Kendaraan' => [['Nama Kendaraan', 'Kapasitas Maksimal (L)'], ['Daihatsu Gran Max', 45]],
        'Kartu QR' => [['Kategori', 'Kendaraan', 'Nomor Plat'], ['Internal', 'Daihatsu Gran Max', 'KT 1111 AA']],
    ]);

    $this->actingAs(admin())->post(route('batch-add.store'), ['file' => $file])
        ->assertSessionHasErrors('file')
        ->assertInertiaFlash('import_errors.0.sheet', 'Kendaraan')
        ->assertInertiaFlash('import_errors.0.message', "Baris 1: Kolom 'Kuota BBM (L)' tidak ditemukan pada baris judul.")
        ->assertInertiaFlash('import_errors.1.sheet', 'Kartu QR')
        ->assertInertiaFlash('import_errors.1.message', "Baris 1: Kolom 'Masa Berlaku' tidak ditemukan pada baris judul.");

    expect(VehicleType::count())->toBe(0);
});

test('accepts 1,000 data rows across both sheets but rejects 1,001', function () {
    VehicleType::factory()->create(['name' => 'Daihatsu Gran Max']);
    $vehicles = fn (int $count) => array_map(fn (int $i) => ["Kendaraan {$i}", 100, 10], range(1, $count));
    $cards = fn (int $count) => array_map(fn (int $i) => ['Internal', 'Daihatsu Gran Max', sprintf('KT %04d AA', $i), 'Lifetime', null, 'Ya'], range(1, $count));

    batchAdd($vehicles(500), $cards(500))->assertSessionHasNoErrors();
    expect(VehicleType::count())->toBe(501)
        ->and(QrCard::count())->toBe(500);

    QrCard::query()->forceDelete();
    batchAdd(array_map(fn (int $i) => ["Baru {$i}", 100, 10], range(1, 501)), $cards(500))
        ->assertSessionHasErrors('file')
        ->assertInertiaFlash('import_errors.0.message', 'Baris 1: File berisi 1.001 baris data; maksimal 1.000 baris.');
});

test('rejects a file that is not an .xlsx', function (UploadedFile $file) {
    $this->actingAs(admin())->post(route('batch-add.store'), ['file' => $file])->assertSessionHasErrors('file');

    expect(VehicleType::count())->toBe(0);
})->with([
    'a csv' => fn () => UploadedFile::fake()->createWithContent('kendaraan.csv', "Nama,Kapasitas\nA,1"),
    'a php script' => fn () => UploadedFile::fake()->createWithContent('kendaraan.php', '<?php echo 1;'),
]);

test('explains a workbook that cannot be read instead of failing', function () {
    $broken = UploadedFile::fake()->createWithContent('batch-add.xlsx', 'bukan workbook');

    $this->actingAs(admin())->post(route('batch-add.store'), ['file' => $broken])
        ->assertSessionHasErrors('file')
        ->assertInertiaFlash('import_errors.0.message', 'Baris 1: File tidak dapat dibaca. Gunakan file Excel (.xlsx) yang valid.');

    expect(VehicleType::count())->toBe(0);
});

test('rejects a file over 2 MB', function () {
    $big = UploadedFile::fake()->create('batch-add.xlsx', 3000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs(admin())->post(route('batch-add.store'), ['file' => $big])->assertSessionHasErrors('file');
});

test('requires a file', function () {
    $this->actingAs(admin())->post(route('batch-add.store'), [])->assertSessionHasErrors(['file' => 'File wajib diisi.']);
});
