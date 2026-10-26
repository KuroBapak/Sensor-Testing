<?php

use App\Models\Personnel;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\Service;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('landing featured works follow featured sort order and stop at six', function () {
    Work::query()->update(['is_featured' => false]);

    $service = Service::first();
    foreach (range(1, 7) as $position) {
        Work::create([
            'service_id' => $service->id,
            'work_title' => "Featured Uji {$position}",
            'work_slug' => "featured-uji-{$position}",
            'is_featured' => true,
            'featured_sort_order' => 10 - $position,
            'sort_order' => $position,
        ]);
    }

    $featured = $this->get(route('home'))->viewData('featuredWorks');

    expect($featured->pluck('work_slug')->all())->toBe([
        'featured-uji-7', 'featured-uji-6', 'featured-uji-5',
        'featured-uji-4', 'featured-uji-3', 'featured-uji-2',
    ]);
});

test('works page sends every work to the view without pagination', function () {
    $service = Service::first();
    $before = Work::count();

    foreach (range(1, 15) as $position) {
        Work::create([
            'service_id' => $service->id,
            'work_title' => "Work Tambahan {$position}",
            'work_slug' => "work-tambahan-{$position}",
            'sort_order' => 100 + $position,
        ]);
    }

    $works = $this->get(route('works'))->viewData('works');

    expect($works)->toHaveCount($before + 15)
        ->and($works->first()->relationLoaded('service'))->toBeTrue();
});

test('works page ignores the legacy service query filter', function () {
    $total = Work::count();

    $works = $this->get(route('works', ['service' => Service::first()->id]))->viewData('works');

    expect($works)->toHaveCount($total);
});

test('portfolio page sends all items, category counts and only active personnel', function () {
    $category = PortfolioCategory::first();
    $before = PortfolioItem::count();

    foreach (range(1, 14) as $position) {
        PortfolioItem::create([
            'portfolio_category_id' => $category->id,
            'title' => "Item Tambahan {$position}",
            'sort_order' => 200 + $position,
        ]);
    }

    $inactive = Personnel::first();
    $inactive->update(['status' => false]);

    $response = $this->get(route('portfolio'));

    expect($response->viewData('portfolioItems'))->toHaveCount($before + 14)
        ->and($response->viewData('portfolioCategories')->firstWhere('id', $category->id)->portfolio_items_count)
        ->toBe($category->portfolioItems()->count())
        ->and($response->viewData('personnel')->pluck('id'))->not->toContain($inactive->id)
        ->and($response->viewData('personnel')->first()->relationLoaded('skills'))->toBeTrue()
        ->and($response->viewData('specialtyClusters'))->not->toBeEmpty();
});

test('portfolio page ignores the legacy category query filter', function () {
    $total = PortfolioItem::count();

    $items = $this->get(route('portfolio', ['category' => PortfolioCategory::first()->id]))->viewData('portfolioItems');

    expect($items)->toHaveCount($total);
});
