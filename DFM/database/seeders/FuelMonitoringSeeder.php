<?php

namespace Database\Seeders;

use App\Models\AlarmLog;
use App\Models\AnomalyLog;
use App\Models\HardwareDevice;
use App\Models\MainTankLog;
use App\Models\MobileTankLog;
use App\Models\RfidTag;
use App\Models\Tank;
use App\Models\TankLevelReading;
use App\Models\Transaction;
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

        // Kalimantan coordinates - INSIDE geofence bounds (lat: -1.665 to -1.685, lon: 113.37 to 113.4)
        $baseLatitude = -1.6720;  // Center of geofence
        $baseLongitude = 113.3850;

        // Get all tanks and devices
        $mainTanks = Tank::where('division', 'main_tank')->get();
        $fuelTankers = Tank::where('division', 'fuel_tanker')->get();
        $browserTanks = Tank::where('division', 'browser_tank')->get();
        $rfidTags = RfidTag::all();

        $this->command->info("Found {$mainTanks->count()} main tanks, {$fuelTankers->count()} fuel tankers, {$browserTanks->count()} browser tanks, {$rfidTags->count()} RFID tags");

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

        // 2. Seed Main Tank Level Monitoring
        foreach ($mainTanks as $tank) {
            $device = HardwareDevice::where('tank_id', $tank->tank_id)
                ->where('device_type', 'main_tank_atg')
                ->first();

            if ($device) {
                for ($i = 96; $i >= 0; $i--) {
                    $timestamp = (clone $now)->subMinutes($i * 15);
                    $baseLevel = $tank->capacity_liters * 0.7;
                    $variance = rand(-2000, 2000);

                    TankLevelReading::create([
                        'tank_id' => $tank->tank_id,
                        'device_id' => $device->device_id,
                        'level_liters' => max(1000, min($tank->capacity_liters, $baseLevel + $variance)),
                        'latitude' => $baseLatitude + (rand(-10, 10) / 10000),
                        'longitude' => $baseLongitude + (rand(-10, 10) / 10000),
                        'satellites' => rand(8, 15),
                        'timestamp' => $timestamp,
                        'server_received_at' => $timestamp,
                        'rtc_out_of_bounds' => false,
                    ]);
                }
            }
        }

        // 3. Seed Mobile Tank Monitoring (Fuel Tankers)
        foreach ($fuelTankers as $index => $tank) {
            $device = HardwareDevice::where('tank_id', $tank->tank_id)
                ->where('device_type', 'mobile_unit')
                ->first();

            if ($device) {
                // Distribute starting positions nicely inside the geofence perimeter
                $currentLat = $baseLatitude + (($index - 1) * 0.003);
                $currentLon = $baseLongitude + (($index - 1) * 0.003);

                for ($i = 144; $i >= 0; $i--) {
                    $timestamp = (clone $now)->subMinutes($i * 10);

                    // Simulate movement strictly inside geofence perimeter (lat: -1.665 to -1.685, lon: 113.37 to 113.4)
                    $currentLat += (rand(-30, 30) / 100000);
                    $currentLon += (rand(-30, 30) / 100000);
                    $currentLat = max(-1.683, min(-1.667, $currentLat));
                    $currentLon = max(113.372, min(113.398, $currentLon));

                    $baseLevel = $tank->capacity_liters * 0.6;
                    $variance = rand(-1500, 1500);

                    TankLevelReading::create([
                        'tank_id' => $tank->tank_id,
                        'device_id' => $device->device_id,
                        'level_liters' => max(500, min($tank->capacity_liters, $baseLevel + $variance)),
                        'latitude' => $currentLat,
                        'longitude' => $currentLon,
                        'satellites' => rand(8, 14),
                        'timestamp' => $timestamp,
                        'server_received_at' => $timestamp,
                        'rtc_out_of_bounds' => false,
                    ]);
                }
            }
        }

        // 4. Seed Mobile Tank Monitoring (Browser Tanks)
        foreach ($browserTanks as $index => $tank) {
            $device = HardwareDevice::where('tank_id', $tank->tank_id)
                ->where('device_type', 'mobile_unit')
                ->first();

            if ($device) {
                // Distribute starting positions nicely inside the geofence perimeter
                $currentLat = $baseLatitude + (($index - 1.5) * 0.002);
                $currentLon = $baseLongitude - (($index - 1.5) * 0.002);

                for ($i = 144; $i >= 0; $i--) {
                    $timestamp = (clone $now)->subMinutes($i * 10);

                    // Simulate movement strictly inside geofence perimeter (lat: -1.665 to -1.685, lon: 113.37 to 113.4)
                    $currentLat += (rand(-30, 30) / 100000);
                    $currentLon += (rand(-30, 30) / 100000);
                    $currentLat = max(-1.683, min(-1.667, $currentLat));
                    $currentLon = max(113.372, min(113.398, $currentLon));

                    $baseLevel = $tank->capacity_liters * 0.5;
                    $variance = rand(-300, 300);

                    TankLevelReading::create([
                        'tank_id' => $tank->tank_id,
                        'device_id' => $device->device_id,
                        'level_liters' => max(100, min($tank->capacity_liters, $baseLevel + $variance)),
                        'latitude' => $currentLat,
                        'longitude' => $currentLon,
                        'satellites' => rand(6, 14),
                        'timestamp' => $timestamp,
                        'server_received_at' => $timestamp,
                        'rtc_out_of_bounds' => false,
                    ]);
                }
                $this->command->info("✓ Created 145 readings for browser tank: {$tank->name}");
            } else {
                $this->command->warn("✗ Device not found for browser tank: {$tank->name} (tank_id: {$tank->tank_id})");
            }
        }

        // 5. Seed Mobile Tank Logs (Legacy)
        $fuelTankerTags = $rfidTags->filter(fn ($t) => str_starts_with($t->tag_id, 'RFID_FT_'));
        $browserTags = $rfidTags->filter(fn ($t) => str_starts_with($t->tag_id, 'RFID_BT_'));

        for ($i = 20; $i > 0; $i--) {
            $time = (clone $now)->subMinutes($i * 5);
            $fuelTankerTag = $fuelTankerTags->isNotEmpty() ? $fuelTankerTags->random() : $rfidTags->random();
            $browserTag = $browserTags->isNotEmpty() ? $browserTags->random() : $rfidTags->random();

            MobileTankLog::create([
                'tank_type' => 'fuel_tanker',
                'rfid' => $fuelTankerTag->tag_id.' ('.$fuelTankerTag->sector.')',
                'waktu' => $time->format('H:i:s'),
                'liter' => rand(100, 600),
            ]);

            MobileTankLog::create([
                'tank_type' => 'browser',
                'rfid' => $browserTag->tag_id.' ('.$browserTag->sector.')',
                'waktu' => $time->format('H:i:s'),
                'liter' => rand(100, 600),
            ]);
        }

        // 6. Seed Transactions (Main Tank fill and dispense operations)
        // Create fill_to_main transactions (vendor filling main tank)
        $this->command->info('Seeding transactions for Main Tank...');
        $mainTankId = $mainTanks->first()?->tank_id;

        if ($mainTankId) {
            for ($i = 48; $i >= 0; $i--) {
                $startTime = (clone $now)->subHours($i)->subMinutes(rand(0, 55));
                $endTime = (clone $startTime)->addMinutes(rand(5, 20));
                $liters = rand(500, 1500);

                // Fill to main tank (vendor supply)
                Transaction::create([
                    'transfer_type' => 'fill_to_main',
                    'main_tank_id' => $mainTankId,
                    'liters' => $liters,
                    'started_at' => $startTime,
                    'ended_at' => $endTime,
                    'server_received_at' => $endTime,
                    'rtc_out_of_bounds' => false,
                    'sync_status' => 'live',
                ]);
            }

            // Create dispense_to_browser transactions (main tank to browser tanks)
            if ($browserTanks->isNotEmpty()) {
                for ($i = 48; $i >= 0; $i--) {
                    $startTime = (clone $now)->subHours($i)->subMinutes(rand(0, 55));
                    $endTime = (clone $startTime)->addMinutes(rand(10, 30));
                    $liters = rand(300, 1000);
                    $browserTank = $browserTanks->random();

                    Transaction::create([
                        'transfer_type' => 'dispense_to_browser',
                        'main_tank_id' => $mainTankId,
                        'tank_id' => $browserTank->tank_id,
                        'liters' => $liters,
                        'started_at' => $startTime,
                        'ended_at' => $endTime,
                        'server_received_at' => $endTime,
                        'rtc_out_of_bounds' => false,
                        'sync_status' => 'live',
                    ]);
                }
            }
            $this->command->info('✓ Created transactions for Main Tank monitoring');
        }

        // 7. Seed Anomaly Logs (Real alarm system using anomaly_logs table)
        $this->command->info('Seeding anomaly logs for Alarm monitoring...');
        $anomalyTypes = ['sudden_change_theft', 'unauthorized_scan', 'geofence_exit'];
        $statusOptions = ['open', 'investigating', 'resolved', 'false_positive'];

        for ($i = 25; $i > 0; $i--) {
            $time = (clone $now)->subHours(rand(1, 72))->subMinutes(rand(1, 59));
            $randomTag = $rfidTags->random();
            $randomTank = collect([$fuelTankers, $browserTanks])->flatten()->random();
            $randomDevice = HardwareDevice::where('tank_id', $randomTank->tank_id)->first();

            // Random latitude/longitude within geofence
            $lat = $baseLatitude + (rand(-150, 150) / 10000);
            $lon = $baseLongitude + (rand(-150, 150) / 10000);
            $lat = max(-1.683, min(-1.667, $lat));
            $lon = max(113.372, min(113.398, $lon));

            $volumeDiff = rand(50, 500) * -1; // Negative = theft/loss
            $observedPercent = rand(50, 150) / 10; // 5.0% to 15.0%
            $thresholdPercent = 5.0;

            AnomalyLog::create([
                'anomaly_type' => $anomalyTypes[array_rand($anomalyTypes)],
                'tank_id' => $randomTank->tank_id,
                'device_id' => $randomDevice?->device_id,
                'tag_id' => $randomTag->tag_id,
                'volume_diff' => $volumeDiff,
                'observed_percent' => $observedPercent,
                'threshold_percent' => $thresholdPercent,
                'sensitivity_mode' => 'medium',
                'anomaly_time' => $time,
                'latitude' => $lat,
                'longitude' => $lon,
                'position_time' => $time,
                'status_investigasi' => $statusOptions[array_rand($statusOptions)],
            ]);
        }
        $this->command->info('✓ Created 25 anomaly logs for Alarm monitoring');

        // 8. Keep legacy tables for backward compatibility (alarm_logs, main_tank_logs, mobile_tank_logs)
        $this->command->info('Seeding legacy tables for backward compatibility...');
        for ($i = 25; $i > 0; $i--) {
            $time = (clone $now)->subHours(rand(1, 72))->subMinutes(rand(1, 59));
            $randomTag = $rfidTags->random();

            AlarmLog::create([
                'rfid' => $randomTag->tag_id.' ('.$randomTag->sector.')',
                'waktu_kejadian' => $time->format('d/m/Y H:i:s'),
                'jumlah_liter' => rand(50, 500),
            ]);
        }

        $this->command->info('✓ Fuel monitoring data seeded successfully!');
    }
}
