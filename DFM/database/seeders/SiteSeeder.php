<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Site::firstOrCreate(
            ['name' => 'PT. Trissan Mining Site'],
            [
                'timezone' => 'Asia/Jakarta',
                'rfid_list_version' => 1,
                'settings' => [
                    'window_minutes' => 30,
                    'eval_grace_minutes' => 5,
                    'settle_seconds' => 180,
                    'position_max_age_minutes' => 15,
                    'rfid_poll_seconds' => 300,
                    'reading_retention_days' => 90,
                    'geofence_exit_confirm_readings' => 2,
                ],
            ]
        );
    }
}
