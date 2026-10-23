<?php

use App\Models\ActivityLog;
use App\Models\QrCard;
use App\Models\Tank;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/** A complete, valid settings form, as the settings page submits it. */
function settingsPayload(array $overrides = []): array
{
    return [
        'reset_period' => 'day',
        'reset_time' => '00:00',
        'week_start_day' => 1,
        'month_reset_day' => 1,
        'timezone' => 'Asia/Jakarta',
        'low_quota_percent' => 20,
        'site_name' => '',
        'tanks' => Tank::orderBy('id')->get()->map(fn (Tank $tank) => ['id' => $tank->id, 'capacity_liters' => $tank->capacity_liters])->all(),
        ...$overrides,
    ];
}

beforeEach(function () {
    Tank::factory()->create(['name' => 'Tangki 1', 'capacity_liters' => 2300]);
    Tank::factory()->create(['name' => 'Tangki 2', 'capacity_liters' => 2800]);
});

test('guests are redirected to the login page', function () {
    $this->get(route('global-settings.edit'))->assertRedirect(route('login'));
    $this->put(route('global-settings.update'), [])->assertRedirect(route('login'));
});

test('shows the settings, the tanks and how many cards follow the reset rules', function () {
    QrCard::factory()->count(2)->create();
    QrCard::factory()->resetOff()->create();
    QrCard::factory()->expired()->create();
    QrCard::factory()->create()->delete();

    $this->actingAs(admin())
        ->get(route('global-settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('global-settings/index')
            ->where('settings.reset_period', 'day')
            ->where('settings.reset_time', '00:00')
            ->where('settings.low_quota_percent', 20)
            ->where('settings.timezone', 'Asia/Jakarta')
            ->has('tanks', 2)
            ->where('tanks.0.capacity_liters', 2300)
            ->where('resetCardCount', 2)
            ->has('timezones', 3));
});

test('lets both roles save settings', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->put(route('global-settings.update'), settingsPayload(['site_name' => 'Site Utara']))
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.message', 'Pengaturan disimpan.');

    expect(app(SettingsService::class)->get('site_name'))->toBe('Site Utara');
})->with(['admin', 'superAdmin']);

test('saves settings that do not touch the reset rules without asking for confirmation', function () {
    $this->actingAs(admin())
        ->put(route('global-settings.update'), settingsPayload([
            'low_quota_percent' => 15,
            'site_name' => 'Site Utara',
            'timezone' => 'Asia/Makassar',
        ]))
        ->assertSessionHasNoErrors();

    $settings = app(SettingsService::class);
    expect($settings->get('low_quota_percent'))->toBe(15)
        ->and($settings->timezone())->toBe('Asia/Makassar');
});

test('saves the tank capacities', function () {
    $tanks = Tank::orderBy('id')->get();

    $this->actingAs(admin())
        ->put(route('global-settings.update'), settingsPayload(['tanks' => [
            ['id' => $tanks[0]->id, 'capacity_liters' => 2500],
            ['id' => $tanks[1]->id, 'capacity_liters' => 3000],
        ]]))
        ->assertSessionHasNoErrors();

    expect($tanks[0]->fresh()->capacity_liters)->toBe(2500.0)
        ->and($tanks[1]->fresh()->capacity_liters)->toBe(3000.0);
});

test('refuses a changed reset period until it is confirmed and says how many cards it affects', function () {
    QrCard::factory()->count(3)->create();

    $this->actingAs(admin())
        ->put(route('global-settings.update'), settingsPayload(['reset_period' => 'week']))
        ->assertSessionHasErrors(['confirm_reset_change' => 'Perubahan aturan reset berlaku untuk 3 kartu dan harus dikonfirmasi.']);

    expect(app(SettingsService::class)->resetPeriod()->value)->toBe('day');
});

test('applies a confirmed change to the reset period at once', function () {
    $this->actingAs(admin())
        ->put(route('global-settings.update'), settingsPayload(['reset_period' => 'week', 'confirm_reset_change' => true]))
        ->assertSessionHasNoErrors();

    $this->get(route('qr-cards.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.resetPeriod', 'week')
            ->where('site.resetUnit', 'minggu')
            ->etc());
});

test('asks for confirmation when the reset time changes', function () {
    $this->actingAs(admin())
        ->put(route('global-settings.update'), settingsPayload(['reset_time' => '06:00']))
        ->assertSessionHasErrors('confirm_reset_change');
});

test('asks for confirmation of a changed week start day only while the period is week', function () {
    $this->actingAs(admin());

    $this->put(route('global-settings.update'), settingsPayload(['week_start_day' => 3]))
        ->assertSessionHasNoErrors();

    $this->put(route('global-settings.update'), settingsPayload(['reset_period' => 'week', 'week_start_day' => 3, 'confirm_reset_change' => true]))
        ->assertSessionHasNoErrors();

    $this->put(route('global-settings.update'), settingsPayload(['reset_period' => 'week', 'week_start_day' => 5]))
        ->assertSessionHasErrors('confirm_reset_change');
});

test('asks for confirmation of a changed month reset day only while the period is month', function () {
    $this->actingAs(admin());

    $this->put(route('global-settings.update'), settingsPayload(['month_reset_day' => 10]))
        ->assertSessionHasNoErrors();

    $this->put(route('global-settings.update'), settingsPayload(['reset_period' => 'month', 'month_reset_day' => 10, 'confirm_reset_change' => true]))
        ->assertSessionHasNoErrors();

    $this->put(route('global-settings.update'), settingsPayload(['reset_period' => 'month', 'month_reset_day' => 15]))
        ->assertSessionHasErrors('confirm_reset_change');
});

test('writes every change to the activity log', function () {
    $user = admin();

    $this->actingAs($user)->put(route('global-settings.update'), settingsPayload(['site_name' => 'Site Utara']));

    $log = $user->fresh()->id;
    $this->assertDatabaseHas('activity_logs', ['action' => 'settings.updated', 'user_id' => $log]);
    expect(ActivityLog::sole()->details['changed']['site_name'])->toBe(['from' => '', 'to' => 'Site Utara']);
});

test('rejects invalid values', function (array $overrides, string $field) {
    $this->actingAs(admin())
        ->put(route('global-settings.update'), settingsPayload($overrides + ['confirm_reset_change' => true]))
        ->assertSessionHasErrors($field);

    expect(app(SettingsService::class)->get('reset_time'))->toBe('00:00');
})->with([
    'unknown period' => [['reset_period' => 'year'], 'reset_period'],
    'reset time without minutes' => [['reset_time' => '25:00'], 'reset_time'],
    'week start day 8' => [['week_start_day' => 8], 'week_start_day'],
    'month reset day 29' => [['month_reset_day' => 29], 'month_reset_day'],
    'month reset day 0' => [['month_reset_day' => 0], 'month_reset_day'],
    'time zone not offered' => [['timezone' => 'Mars/Base'], 'timezone'],
    'low quota 0 percent' => [['low_quota_percent' => 0], 'low_quota_percent'],
    'low quota above 100 percent' => [['low_quota_percent' => 101], 'low_quota_percent'],
]);

test('rejects a tank capacity that is not positive', function () {
    $tanks = Tank::orderBy('id')->get();

    $this->actingAs(admin())
        ->put(route('global-settings.update'), settingsPayload(['tanks' => [
            ['id' => $tanks[0]->id, 'capacity_liters' => 0],
            ['id' => $tanks[1]->id, 'capacity_liters' => 2800],
        ]]))
        ->assertSessionHasErrors('tanks.0.capacity_liters');
});

test('uploads a logo, keeps it on disk and shares its URL as branding', function () {
    Storage::fake('local');
    $this->actingAs(admin());

    $this->put(route('global-settings.update'), settingsPayload(['logo' => UploadedFile::fake()->image('logo.png', 120, 120)]))
        ->assertSessionHasNoErrors();

    $path = app(SettingsService::class)->get('logo_path');
    Storage::disk('local')->assertExists($path);

    $this->get(route('tank.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('branding.logoUrl', '/site-logo?v='.basename($path))
            ->etc());
});

test('a new logo replaces the old file', function () {
    Storage::fake('local');
    $this->actingAs(admin());
    $this->put(route('global-settings.update'), settingsPayload(['logo' => UploadedFile::fake()->image('first.png')]));
    $first = app(SettingsService::class)->get('logo_path');

    $this->put(route('global-settings.update'), settingsPayload(['logo' => UploadedFile::fake()->image('second.jpg')]))
        ->assertSessionHasNoErrors();

    $second = app(SettingsService::class)->get('logo_path');
    expect($second)->not->toBe($first);
    Storage::disk('local')->assertMissing($first);
    Storage::disk('local')->assertExists($second);
});

test('removes the logo and its file', function () {
    Storage::fake('local');
    $this->actingAs(admin());
    $this->put(route('global-settings.update'), settingsPayload(['logo' => UploadedFile::fake()->image('logo.png')]));
    $path = app(SettingsService::class)->get('logo_path');

    $this->put(route('global-settings.update'), settingsPayload(['remove_logo' => true]))->assertSessionHasNoErrors();

    expect(app(SettingsService::class)->get('logo_path'))->toBe('');
    Storage::disk('local')->assertMissing($path);
    $this->get(route('site-logo'))->assertNotFound();
});

test('keeps the logo when other settings are saved without a file', function () {
    Storage::fake('local');
    $this->actingAs(admin());
    $this->put(route('global-settings.update'), settingsPayload(['logo' => UploadedFile::fake()->image('logo.png')]));
    $path = app(SettingsService::class)->get('logo_path');

    $this->put(route('global-settings.update'), settingsPayload(['site_name' => 'Site Utara']))->assertSessionHasNoErrors();

    expect(app(SettingsService::class)->get('logo_path'))->toBe($path);
    Storage::disk('local')->assertExists($path);
});

test('rejects a logo that is not a small png, jpg, webp or svg image', function (UploadedFile $file) {
    Storage::fake('local');

    $this->actingAs(admin())
        ->put(route('global-settings.update'), settingsPayload(['logo' => $file]))
        ->assertSessionHasErrors('logo');

    expect(app(SettingsService::class)->get('logo_path'))->toBe('')
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();
})->with([
    'a php script' => fn () => UploadedFile::fake()->createWithContent('logo.php', '<?php echo 1;'),
    'a pdf' => fn () => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
    'a gif' => fn () => UploadedFile::fake()->image('logo.gif'),
    'an image over 1 MB' => fn () => UploadedFile::fake()->image('logo.png')->size(2000),
]);

test('the branding is the app name and no logo until a site name and logo are set', function () {
    $this->actingAs(admin())
        ->get(route('tank.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('branding.name', config('app.name'))
            ->where('branding.logoUrl', null)
            ->etc());

    setSettings(['site_name' => 'PT Contoh Mining Site']);

    $this->get(route('tank.index'))
        ->assertInertia(fn (Assert $page) => $page->where('branding.name', 'PT Contoh Mining Site')->etc());
});

test('guests get the branding on the login page too', function () {
    setSettings(['site_name' => 'PT Contoh Mining Site']);

    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->where('branding.name', 'PT Contoh Mining Site')->etc());
});
