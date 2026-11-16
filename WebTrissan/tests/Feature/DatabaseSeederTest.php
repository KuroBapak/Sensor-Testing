<?php

use App\Models\AboutCompany;
use App\Models\CorporateAccreditation;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Personnel;
use App\Models\PortfolioItem;
use App\Models\ProductCertification;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('db:seed runs every seeder in the seeders folder, so a new one cannot be forgotten', function () {
    $called = file_get_contents(database_path('seeders/DatabaseSeeder.php'));
    $missing = [];

    foreach (glob(database_path('seeders/*.php')) as $file) {
        $class = basename($file, '.php');

        if ($class === 'DatabaseSeeder' || ! preg_match('/class\s+'.$class.'\s+extends\s+Seeder\b/', (string) file_get_contents($file))) {
            continue;
        }

        if (! str_contains($called, $class.'::class')) {
            $missing[] = $class;
        }
    }

    expect($missing)->toBe([]);
});

test('db:seed fills every part of the site, including the demo copy outside the test environment', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

    expect(GlobalSetting::query()->count())->toBe(1)
        ->and(PageSetting::query()->pluck('page')->sort()->values()->all())->toBe(['about', 'landing', 'licenses', 'portfolio', 'works'])
        ->and(AboutCompany::query()->count())->toBe(1)
        ->and(ServiceCluster::query()->count())->toBe(4)
        ->and(Service::query()->count())->toBe(12)
        ->and(Work::query()->count())->toBeGreaterThan(0)
        ->and(CorporateAccreditation::query()->count())->toBeGreaterThan(0)
        ->and(ProductCertification::query()->count())->toBeGreaterThan(0)
        ->and(PortfolioItem::query()->count())->toBeGreaterThan(0)
        ->and(Personnel::query()->count())->toBeGreaterThan(0)
        ->and(Service::query()->get()->every(fn (Service $service): bool => $service->hasLandingContent()))->toBeTrue()
        ->and(GlobalSetting::current()->whatsapp_number)->not->toBeNull();
});

test('running db:seed twice does not duplicate anything', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $this->seed(DatabaseSeeder::class);
    $counts = [Service::query()->count(), Work::query()->count(), PortfolioItem::query()->count(), Personnel::query()->count(), CorporateAccreditation::query()->count()];

    $this->seed(DatabaseSeeder::class);

    expect([Service::query()->count(), Work::query()->count(), PortfolioItem::query()->count(), Personnel::query()->count(), CorporateAccreditation::query()->count()])->toBe($counts);
});
