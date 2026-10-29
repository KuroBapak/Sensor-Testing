<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Work;
use Illuminate\Database\Seeder;

class WorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Projects are taken from the company's previous website (trissan.co.id/berita).
     */
    public function run(): void
    {
        $services = Service::all()->keyBy('slug');

        foreach ($this->works() as $item) {
            $service = $services[$item['service_slug']] ?? null;
            if ($service) {
                $item['service_id'] = $service->id;
                unset($item['service_slug']);
                Work::updateOrCreate(['work_slug' => $item['work_slug']], $item);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function works(): array
    {
        return [
            [
                'service_slug' => 'mechanical-electrical',
                'work_title' => 'Fasum OP Mainline Factory Water Meter & Bypass Chiller Installation',
                'work_slug' => 'fasum-op-water-meter-bypass-chiller',
                'is_featured' => true,
                'featured_sort_order' => 1,
                'work_image' => SeedMedia::image('work-1'),
                'gallery_images' => [SeedMedia::image('work-1')],
                'top_badge' => 'INSTALASI',
                'bottom_badge' => 'NOV 2023',
                'serial_number' => 'PT ASTRA HONDA MOTOR',
                'client_sector' => 'AUTOMOTIVE',
                'short_description' => 'Instalasi mainline water meter fasum OP pabrik dan bypass chiller di PT Astra Honda Motor Plant 3 Cibitung.',
                'full_description' => "Lingkup pekerjaan: instalasi mainline water meter fasum OP pabrik dan instalasi bypass chiller.\n\nLokasi pekerjaan: PT. Astra Honda Motor - Plant 3 MM2100 Cibitung.",
                'highlight_tags' => ['Water Meter', 'Bypass Chiller', 'Plant 3 Cibitung'],
                'production_date' => '2023-11-27',
                'sort_order' => 1,
            ],
            [
                'service_slug' => 'mechanical-electrical',
                'work_title' => 'Fire Hydrant System',
                'work_slug' => 'fire-hydrant-system',
                'is_featured' => true,
                'featured_sort_order' => 2,
                'work_image' => SeedMedia::image('work-2'),
                'gallery_images' => [SeedMedia::image('work-2')],
                'top_badge' => 'FIRE PROTECTION',
                'bottom_badge' => 'NOV 2023',
                'serial_number' => 'PT MAYORA INDAH, TBK.',
                'client_sector' => 'FOOD & BEVERAGE',
                'short_description' => 'Manufaktur sistem fire hydrant untuk PT Mayora Indah, Tbk.',
                'full_description' => "Lingkup pekerjaan: manufaktur fire hydrant system.\n\nLokasi pekerjaan: PT. Mayora Indah, Tbk.",
                'highlight_tags' => ['Fire Hydrant', 'Manufaktur Sistem', 'Mayora Indah'],
                'production_date' => '2023-11-27',
                'sort_order' => 2,
            ],
            [
                'service_slug' => 'mechanical-electrical',
                'work_title' => 'Chiller Duct Bypass',
                'work_slug' => 'chiller-duct-bypass',
                'is_featured' => true,
                'featured_sort_order' => 3,
                'work_image' => SeedMedia::image('work-3'),
                'gallery_images' => [SeedMedia::image('work-3')],
                'top_badge' => 'PIPING',
                'bottom_badge' => 'NOV 2023',
                'serial_number' => 'PT ASTRA HONDA MOTOR',
                'client_sector' => 'AUTOMOTIVE',
                'short_description' => 'Manufaktur pipa dingin / bypass chiller di PT Astra Honda Motor Plant 3 Cibitung.',
                'full_description' => "Lingkup pekerjaan: manufaktur pipa dingin (cold pipe) / bypass chiller.\n\nLokasi pekerjaan: PT Astra Honda Motor - Plant 3 Cibitung (Kawasan Industri MM2100).",
                'highlight_tags' => ['Cold Pipe', 'Chiller Bypass', 'Plant 3 Cibitung'],
                'production_date' => '2023-11-27',
                'sort_order' => 3,
            ],
            [
                'service_slug' => 'material-handling',
                'work_title' => 'Drying Conveyor',
                'work_slug' => 'drying-conveyor',
                'is_featured' => true,
                'featured_sort_order' => 4,
                'work_image' => SeedMedia::image('work-4'),
                'gallery_images' => [SeedMedia::image('work-4')],
                'top_badge' => 'CONVEYOR',
                'bottom_badge' => 'NOV 2023',
                'serial_number' => 'PT NIHON PLAST',
                'client_sector' => 'PLASTICS & COMPONENTS',
                'short_description' => 'Pembuatan drying conveyor untuk lini produksi PT Nihon Plast.',
                'full_description' => 'Proyek drying conveyor untuk PT. Nihon Plast.',
                'highlight_tags' => ['Drying Conveyor', 'Nihon Plast'],
                'production_date' => '2023-11-24',
                'sort_order' => 4,
            ],
            [
                'service_slug' => 'electrical-installation',
                'work_title' => 'Conveyor Maintenance',
                'work_slug' => 'conveyor-maintenance',
                'is_featured' => true,
                'featured_sort_order' => 5,
                'work_image' => SeedMedia::image('work-5'),
                'gallery_images' => [SeedMedia::image('work-5')],
                'top_badge' => 'MAINTENANCE',
                'bottom_badge' => 'NOV 2023',
                'serial_number' => 'PT MIKUNI & PT NIHON PLAST',
                'client_sector' => 'MANUFACTURING',
                'short_description' => 'Perawatan konveyor: penggantian belt roller drive, connection belt, dan perpanjangan frame konveyor.',
                'full_description' => "Lingkup pekerjaan:\n- Belt replacement roller drive\n- Connection belt maintenance\n- Extended conveyor frame & connection belt maintenance\n\nProyek: PT. Mikuni & PT. Nihon Plast.",
                'highlight_tags' => ['Belt Replacement', 'Roller Drive', 'Extended Frame'],
                'production_date' => '2023-11-24',
                'sort_order' => 5,
            ],
            [
                'service_slug' => 'material-handling',
                'work_title' => 'Material Handling Conveyor for Automation',
                'work_slug' => 'material-handling-conveyor-for-automation',
                'is_featured' => true,
                'featured_sort_order' => 6,
                'work_image' => SeedMedia::image('work-6'),
                'gallery_images' => [SeedMedia::image('work-6')],
                'top_badge' => 'AUTOMATION',
                'bottom_badge' => 'NOV 2023',
                'serial_number' => 'PT INDOFOOD CBP PURWAKARTA',
                'client_sector' => 'FOOD & BEVERAGE',
                'short_description' => 'Conveyor feeding untuk lini packaging biskuit di PT Indofood CBP Purwakarta.',
                'full_description' => "Conveyor feeding untuk packaging line biskuit.\n\nProyek: PT. Indofood CBP Purwakarta.",
                'highlight_tags' => ['Conveyor Feeding', 'Packaging Line', 'Biskuit'],
                'production_date' => '2023-11-24',
                'sort_order' => 6,
            ],
            [
                'service_slug' => 'material-handling',
                'work_title' => 'Material Handling Conveyor',
                'work_slug' => 'material-handling-conveyor',
                'is_featured' => false,
                'featured_sort_order' => 0,
                'work_image' => SeedMedia::image('work-7'),
                'gallery_images' => [SeedMedia::image('work-7')],
                'top_badge' => 'CONVEYOR',
                'bottom_badge' => 'NOV 2023',
                'serial_number' => 'PT YASKAWA ELECTRICAL - PT AHM',
                'client_sector' => 'AUTOMOTIVE',
                'short_description' => 'Tending conveyor untuk auto cutting suku cadang otomotif (PT Yaskawa Electrical - PT AHM).',
                'full_description' => "Tending conveyor untuk auto cutting automotive parts.\n\nProyek: PT. Yaskawa Electrical - PT. AHM.",
                'highlight_tags' => ['Tending Conveyor', 'Auto Cutting', 'Automotive Parts'],
                'production_date' => '2023-11-24',
                'sort_order' => 7,
            ],
        ];
    }
}
