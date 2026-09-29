<?php

namespace Database\Seeders;

use App\Models\Geofence;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlaceholderDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $site = Site::first() ?? Site::create([
            'name' => 'PT. Trissan Mining Site',
            'location' => 'Kalimantan Tengah',
            'timezone' => 'Asia/Jakarta',
        ]);

        $admin = User::first() ?? User::factory()->create();

        // 1. Seed Placeholder Geofences
        $geofences = [
            [
                'site_id' => $site->id,
                'name' => 'Mining Site Main Perimeter',
                'applies_to' => 'browser_tank,fuel_tanker',
                'is_active' => true,
                'coordinates' => [
                    [-1.6650, 113.3700],
                    [-1.6650, 113.4000],
                    [-1.6850, 113.4000],
                    [-1.6850, 113.3700],
                ],
                'created_by' => $admin->id,
            ],
            [
                'site_id' => $site->id,
                'name' => 'Fuel Depot & Workshop Zone',
                'applies_to' => 'fuel_tanker',
                'is_active' => true,
                'coordinates' => [
                    [-1.6720, 113.3780],
                    [-1.6720, 113.3920],
                    [-1.6800, 113.3920],
                    [-1.6800, 113.3780],
                ],
                'created_by' => $admin->id,
            ],
        ];

        foreach ($geofences as $geo) {
            Geofence::updateOrCreate(
                ['name' => $geo['name']],
                $geo
            );
        }

        $this->command->info('✓ Seeded 2 placeholder geofences');
    }
}
