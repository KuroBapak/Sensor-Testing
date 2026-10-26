<?php

namespace Database\Seeders;

use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedPortfolioItems();
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Aerospace & Defense', 'slug' => 'aerospace-defense', 'sort_order' => 1],
            ['name' => 'Oil & Gas Energy', 'slug' => 'oil-gas-energy', 'sort_order' => 2],
            ['name' => 'Automotive & Heavy Equipment', 'slug' => 'automotive-heavy-equipment', 'sort_order' => 3],
            ['name' => 'Electronics & Semiconductor', 'slug' => 'electronics-semiconductor', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            PortfolioCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }

    private function seedPortfolioItems(): void
    {
        $categories = PortfolioCategory::all()->keyBy('slug');

        $items = [
            ['category_slug' => 'aerospace-defense', 'title' => 'F-16 Landing Gear Housing', 'image' => SeedMedia::image('portfolio/aero-1'), 'industry_tag' => 'AEROSPACE T1', 'description' => 'Presisi komponen struktural landing gear pesawat tempur.', 'sort_order' => 1],
            ['category_slug' => 'aerospace-defense', 'title' => 'Turbine Engine Casing', 'image' => SeedMedia::image('portfolio/aero-2'), 'industry_tag' => 'AVIATION', 'description' => 'Casing turbin berbahan titanium Ti-6Al-4V kualitas aerospace.', 'sort_order' => 2],
            ['category_slug' => 'oil-gas-energy', 'title' => 'API 6A Wellhead Valve Body', 'image' => SeedMedia::image('portfolio/oil-1'), 'industry_tag' => 'UPSTREAM OIL', 'description' => 'Valve body tekanan tinggi 15.000 PSI untuk aplikasi sumur minyak.', 'sort_order' => 3],
            ['category_slug' => 'oil-gas-energy', 'title' => 'Subsea Manifold Block', 'image' => SeedMedia::image('portfolio/oil-2'), 'industry_tag' => 'OFFSHORE', 'description' => 'Manifold hidrolik untuk aplikasi subsea hingga kedalaman 3000 meter.', 'sort_order' => 4],
            ['category_slug' => 'automotive-heavy-equipment', 'title' => 'Excavator Swing Bearing Housing', 'image' => SeedMedia::image('portfolio/auto-1'), 'industry_tag' => 'HEAVY MINING', 'description' => 'Housing swing bearing excavator 50 ton dengan presisi machining CNC.', 'sort_order' => 5],
            ['category_slug' => 'automotive-heavy-equipment', 'title' => 'Transmission Gearbox Casing', 'image' => SeedMedia::image('portfolio/auto-2'), 'industry_tag' => 'AUTOMOTIVE T1', 'description' => 'Casing gearbox otomotif paduan aluminium A356-T6.', 'sort_order' => 6],
            ['category_slug' => 'electronics-semiconductor', 'title' => 'Wafer Handling Robot Arm', 'image' => SeedMedia::image('portfolio/elec-1'), 'industry_tag' => 'SEMICONDUCTOR', 'description' => 'Robot arm presisi ultra-high untuk handling wafer silikon cleanroom.', 'sort_order' => 7],
            ['category_slug' => 'electronics-semiconductor', 'title' => 'Precision Optical Lens Mount', 'image' => SeedMedia::image('portfolio/elec-2'), 'industry_tag' => 'OPTICS', 'description' => 'Mounting lensa optik presisi sub-micron untuk sistem laser industri.', 'sort_order' => 8],
        ];

        foreach ($items as $item) {
            $category = $categories[$item['category_slug']] ?? null;
            if ($category) {
                $item['portfolio_category_id'] = $category->id;
                unset($item['category_slug']);
                PortfolioItem::updateOrCreate(['title' => $item['title']], $item);
            }
        }
    }
}
