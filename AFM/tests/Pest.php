<?php

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Tests use factories, not the demo seeder, so results never depend on dummy data.
|
*/

function admin(): User
{
    return User::factory()->admin()->create();
}

function superAdmin(): User
{
    return User::factory()->superAdmin()->create();
}

/** Change global settings the way the settings page does. */
function setSettings(array $values): void
{
    app(SettingsService::class)->update($values);
}

/**
 * A real .xlsx upload with several sheets, e.g. ['Kendaraan' => $rows, 'Kartu QR' => $rows].
 * The first row of every sheet is its header.
 */
function workbookUpload(array $sheets, string $name = 'batch-add.xlsx'): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->removeSheetByIndex(0);

    foreach ($sheets as $title => $rows) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($title);
        $sheet->fromArray($rows);
    }

    $path = tempnam(sys_get_temp_dir(), 'xlsx');
    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile($path, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
}
