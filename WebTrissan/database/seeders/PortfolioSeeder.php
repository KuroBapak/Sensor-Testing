<?php

namespace Database\Seeders;

use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Items mirror the projects published on the company's previous website (trissan.co.id/berita).
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedPortfolioItems();
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Otomotif & Manufaktur', 'slug' => 'otomotif-manufaktur', 'sort_order' => 1],
            ['name' => 'Makanan & Minuman', 'slug' => 'makanan-minuman', 'sort_order' => 2],
            ['name' => 'Plastik & Komponen', 'slug' => 'plastik-komponen', 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            PortfolioCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }

    private function seedPortfolioItems(): void
    {
        $categories = PortfolioCategory::all()->keyBy('slug');

        $items = [
            ['category_slug' => 'otomotif-manufaktur', 'title' => 'Mainline Water Meter & Bypass Chiller Installation', 'image' => SeedMedia::image('portfolio/auto-1'), 'industry_tag' => 'ASTRA HONDA MOTOR', 'description' => 'Instalasi mainline water meter fasum OP pabrik dan bypass chiller di Plant 3 Cibitung (MM2100).', 'sort_order' => 1],
            ['category_slug' => 'otomotif-manufaktur', 'title' => 'Chiller Duct Bypass', 'image' => SeedMedia::image('portfolio/auto-2'), 'industry_tag' => 'ASTRA HONDA MOTOR', 'description' => 'Manufaktur pipa dingin / bypass chiller di PT Astra Honda Motor Plant 3 Cibitung.', 'sort_order' => 2],
            ['category_slug' => 'otomotif-manufaktur', 'title' => 'Tending Conveyor Auto Cutting Automotive Parts', 'image' => SeedMedia::image('portfolio/auto-3'), 'industry_tag' => 'YASKAWA - AHM', 'description' => 'Material handling conveyor untuk proses auto cutting suku cadang otomotif.', 'sort_order' => 3],
            ['category_slug' => 'makanan-minuman', 'title' => 'Fire Hydrant System', 'image' => SeedMedia::image('portfolio/food-1'), 'industry_tag' => 'MAYORA INDAH', 'description' => 'Manufaktur sistem fire hydrant untuk PT Mayora Indah, Tbk.', 'sort_order' => 4],
            ['category_slug' => 'makanan-minuman', 'title' => 'Conveyor Feeding Packaging Line Biskuit', 'image' => SeedMedia::image('portfolio/food-2'), 'industry_tag' => 'INDOFOOD CBP PURWAKARTA', 'description' => 'Material handling conveyor untuk otomasi lini packaging biskuit.', 'sort_order' => 5],
            ['category_slug' => 'plastik-komponen', 'title' => 'Drying Conveyor', 'image' => SeedMedia::image('portfolio/plastic-1'), 'industry_tag' => 'NIHON PLAST', 'description' => 'Pembuatan drying conveyor untuk lini produksi PT Nihon Plast.', 'sort_order' => 6],
            ['category_slug' => 'plastik-komponen', 'title' => 'Conveyor Maintenance', 'image' => SeedMedia::image('portfolio/plastic-2'), 'industry_tag' => 'MIKUNI & NIHON PLAST', 'description' => 'Penggantian belt roller drive, perawatan connection belt, dan perpanjangan frame konveyor.', 'sort_order' => 7],
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
