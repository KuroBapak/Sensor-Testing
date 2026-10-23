<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the demo data: `php artisan migrate:fresh --seed`.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            UserSeeder::class,
            TankSeeder::class,
            VehicleTypeSeeder::class,
            QrCardSeeder::class,
        ]);
    }
}
