<?php

namespace Database\Seeders;

use App\Models\AlarmLog;
use App\Models\MainTankLog;
use App\Models\MobileTankLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FuelMonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Seed Main Tank Logs (Last 12 hours)
        for ($i = 12; $i >= 0; $i--) {
            $time = (clone $now)->subHours($i);
            MainTankLog::create([
                'waktu' => $time->format('H:00'),
                'total_liter' => rand(15000, 20000),
                'liter_masuk' => rand(500, 1500),
                'liter_keluar' => rand(300, 1000),
            ]);
        }

        // 2. Seed Mobile Tank Logs (Last 20 updates for each type)
        for ($i = 20; $i > 0; $i--) {
            $time = (clone $now)->subMinutes($i * 5);

            // Fuel Tanker
            MobileTankLog::create([
                'tank_type' => 'fuel_tanker',
                'rfid' => strtoupper(substr(md5(rand()), 0, 5)).'(Sector '.rand(1, 10).')',
                'waktu' => $time->format('H:i:s'),
                'liter' => rand(100, 600),
            ]);

            // Browser Tank
            MobileTankLog::create([
                'tank_type' => 'browser',
                'rfid' => strtoupper(substr(md5(rand()), 0, 5)).'(Sector '.rand(1, 10).')',
                'waktu' => $time->format('H:i:s'),
                'liter' => rand(100, 600),
            ]);
        }

        // 3. Seed Alarm Logs (Last 10 alarms)
        for ($i = 10; $i > 0; $i--) {
            $time = (clone $now)->subHours(rand(1, 48))->subMinutes(rand(1, 59));
            AlarmLog::create([
                'rfid' => strtoupper(substr(md5(rand()), 0, 5)).'(Sector '.rand(1, 10).')',
                'waktu_kejadian' => $time->format('d/m/Y H:i:s'),
                'jumlah_liter' => rand(50, 350),
            ]);
        }
    }
}
