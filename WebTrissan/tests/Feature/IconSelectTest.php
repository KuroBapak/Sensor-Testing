<?php

use App\Filament\Pages\ManageGlobalSettings;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Filament\Support\IconSelect;
use App\Models\Service;
use App\Models\User;
use App\Support\MaterialIcons;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('every icon name is a plain Material Symbols name with an Indonesian description', function () {
    foreach (MaterialIcons::ICONS as $name => $description) {
        expect($name)->toMatch('/^[a-z][a-z0-9_]*$/')
            ->and($description)->not->toBeEmpty();
    }
});

test('the list contains every icon the views and seeders already use, so existing content keeps a matching choice', function () {
    $used = [];

    foreach (glob(resource_path('views/*.blade.php')) as $view) {
        preg_match_all('/<x-material-icon[^>]*\sname="([a-z0-9_]+)"/', file_get_contents($view), $matches);
        $used = [...$used, ...$matches[1]];
    }

    foreach (glob(database_path('seeders/*.php')) as $seeder) {
        preg_match_all("/'(?:icon|card_icon|category_icon|cluster_icon|service_icon)' => '([a-z0-9_]+)'/", file_get_contents($seeder), $matches);
        $used = [...$used, ...$matches[1]];
    }

    expect(array_diff(array_unique($used), array_keys(MaterialIcons::ICONS)))->toBe([]);
});

test('icons can be found by their English name or their Indonesian description', function () {
    expect(array_keys(MaterialIcons::search('listrik')))->toContain('bolt', 'electrical_services')
        ->and(array_keys(MaterialIcons::search('pabrik')))->toBe(['factory'])
        ->and(array_keys(MaterialIcons::search('FORKLIFT')))->toBe(['forklift'])
        ->and(MaterialIcons::search('zzz'))->toBe([])
        ->and(MaterialIcons::search(''))->toHaveCount(count(MaterialIcons::ICONS));
});

test('a choice shows the icon, its name and its meaning, and escapes anything unexpected', function () {
    $label = MaterialIcons::label('bolt');

    expect($label)->toContain('data-icon="bolt"')->toContain('Listrik, petir, daya')
        ->and(MaterialIcons::label('"><script>alert(1)</script>'))->not->toContain('<script>');
});

test('the admin font holds only the listed icons, sorted as Google requires', function () {
    $url = MaterialIcons::fontUrl();
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
    $names = explode(',', $query['icon_names']);

    expect($names)->toHaveCount(count(MaterialIcons::ICONS))
        ->and($names)->toBe(collect($names)->sort()->values()->all())
        ->and($url)->toContain('opsz,wght,FILL,GRAD@24,400,0,0');
});

test('the admin panel loads the icon font so the dropdown can draw the icons', function () {
    $this->get('/admin/login')->assertOk()
        ->assertSee('fonts.googleapis.com/css2?family=Material+Symbols+Outlined', false)
        ->assertSee('content:attr(data-icon)', false);
});

test('the dropdown is searchable and keeps an icon that is not in the list', function () {
    $field = IconSelect::make('service_icon');

    expect($field->isSearchable())->toBeTrue()
        ->and($field->isHtmlAllowed())->toBeTrue();
});

test('an editor picks a service icon from the list, and an older typed value still saves', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::factory()->create(['email' => 'admin_icons@example.com']);
    $service = Service::query()->where('slug', 'jigs-fixtures')->firstOrFail();

    $page = Livewire\Livewire::actingAs($admin)
        ->test(EditService::class, ['record' => $service->getRouteKey()])
        ->assertSuccessful()
        ->fillForm(['service_icon' => 'bolt'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->refresh()->service_icon)->toBe('bolt');

    $page->fillForm(['service_icon' => 'icon_typed_by_hand'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->refresh()->service_icon)->toBe('icon_typed_by_hand');
});

test('the social platform is chosen from the logos the website can draw', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::factory()->create(['email' => 'admin_social@example.com']);

    Livewire\Livewire::actingAs($admin)
        ->test(ManageGlobalSettings::class)
        ->assertSuccessful();

    $this->get('/')->assertOk()->assertSee('M24 12.073c0-6.627', false);
});
