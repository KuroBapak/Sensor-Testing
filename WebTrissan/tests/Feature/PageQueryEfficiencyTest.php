<?php

use App\Models\CertificationCategory;
use App\Models\CorporateAccreditation;
use App\Models\Personnel;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\ProductCertification;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function countQueriesFor(string $routeName): int
{
    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    test()->get(route($routeName))->assertOk();

    return $queries;
}

test('public pages issue the same number of queries regardless of how many records exist', function (string $routeName) {
    countQueriesFor($routeName); // warm up so one-off queries do not skew the comparison
    $before = countQueriesFor($routeName);

    $service = Service::first();
    $category = CertificationCategory::first();
    $skills = Skill::take(2)->get();

    foreach (range(1, 12) as $n) {
        $work = Work::create(['service_id' => $service->id, 'work_title' => "Work N+1 {$n}", 'work_slug' => "work-n-plus-1-{$n}", 'is_featured' => $n <= 2]);
        PortfolioItem::create(['portfolio_category_id' => PortfolioCategory::first()->id, 'title' => "Item N+1 {$n}"]);
        CorporateAccreditation::create(['certification_category_id' => $category->id, 'standard_name' => "ISO N+1 {$n}", 'scope_detail' => 'Lingkup']);
        ProductCertification::create(['slug' => "n-plus-1-{$n}", 'product_name' => "Produk N+1 {$n}", 'work_id' => $work->id]);
        Personnel::create([
            'specialty_cluster_id' => Personnel::first()->specialty_cluster_id,
            'registration_id' => "MP-N1-{$n}",
            'job_role' => "Peran N+1 {$n}",
        ])->skills()->sync($skills->pluck('id'));
    }

    expect(countQueriesFor($routeName))->toBe($before);
})->with(['home', 'works', 'portfolio', 'licenses', 'about']);
