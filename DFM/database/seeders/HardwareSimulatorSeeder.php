<?php

namespace Database\Seeders;

use App\Models\HardwareDevice;
use App\Models\RfidTag;
use App\Models\Site;
use App\Models\Tank;
use Illuminate\Database\Seeder;

class HardwareSimulatorSeeder extends Seeder
{
    /**
     * Seed hardware devices, tanks, and RFID tags for DummyHardSim testing.
     * Tokens match those in DummyHardSim/config.json
     */
    public function run(): void
    {
        $site = Site::first();
        if (! $site) {
            $this->command->error('No site found. Run SiteSeeder first.');

            return;
        }

        // 1. Create Tanks
        $mainTank1 = Tank::updateOrCreate(
            ['tank_id' => 1],
            [
                'site_id' => $site->id,
                'name' => 'Main Tank 1 - Central',
                'division' => 'main_tank',
                'capacity_liters' => 30000,
            ]
        );

        $mainTank2 = Tank::updateOrCreate(
            ['tank_id' => 2],
            [
                'site_id' => $site->id,
                'name' => 'Main Tank 2 - North',
                'division' => 'main_tank',
                'capacity_liters' => 25000,
            ]
        );

        $fuelTanker1 = Tank::updateOrCreate(
            ['tank_id' => 3],
            [
                'site_id' => $site->id,
                'name' => 'Fuel Tanker FT-001',
                'division' => 'fuel_tanker',
                'capacity_liters' => 10000,
            ]
        );

        $fuelTanker2 = Tank::updateOrCreate(
            ['tank_id' => 4],
            [
                'site_id' => $site->id,
                'name' => 'Fuel Tanker FT-002',
                'division' => 'fuel_tanker',
                'capacity_liters' => 10000,
            ]
        );

        $fuelTanker3 = Tank::updateOrCreate(
            ['tank_id' => 5],
            [
                'site_id' => $site->id,
                'name' => 'Fuel Tanker FT-003',
                'division' => 'fuel_tanker',
                'capacity_liters' => 8000,
            ]
        );

        $browserTank1 = Tank::updateOrCreate(
            ['tank_id' => 6],
            [
                'site_id' => $site->id,
                'name' => 'Browser Tank BT-001',
                'division' => 'browser_tank',
                'capacity_liters' => 2000,
            ]
        );

        $browserTank2 = Tank::updateOrCreate(
            ['tank_id' => 7],
            [
                'site_id' => $site->id,
                'name' => 'Browser Tank BT-002',
                'division' => 'browser_tank',
                'capacity_liters' => 2000,
            ]
        );

        $browserTank3 = Tank::updateOrCreate(
            ['tank_id' => 8],
            [
                'site_id' => $site->id,
                'name' => 'Browser Tank BT-003',
                'division' => 'browser_tank',
                'capacity_liters' => 2000,
            ]
        );

        $browserTank4 = Tank::updateOrCreate(
            ['tank_id' => 9],
            [
                'site_id' => $site->id,
                'name' => 'Browser Tank BT-004',
                'division' => 'browser_tank',
                'capacity_liters' => 1500,
            ]
        );

        $this->command->info('✓ Created 9 tanks (2 main, 3 fuel tankers, 4 browser tanks)');

        // 2. Create Hardware Devices (all assigned to at least 1 registered tank)
        // Hardware Types:
        // - Dispense Line (RUT956) -> dispense_line
        // - Fill Line (RUT956) -> fill_line
        // - Main Tank ATG (RUT956) -> main_tank_atg
        // - Mobile Unit (FMC225) -> mobile_unit

        $devices = [
            // Main Tank ATG Devices (RUT956)
            [
                'device_id' => 'SIM_ATG_001',
                'device_type' => 'main_tank_atg',
                'tank_id' => $mainTank1->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'e5g34tg4tg4tg5rg4t'),
            ],
            [
                'device_id' => 'SIM_ATG_002',
                'device_type' => 'main_tank_atg',
                'tank_id' => $mainTank2->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'atg_token_central_2'),
            ],

            // Fill Line Devices (RUT956) - assigned to main tanks
            [
                'device_id' => 'SIM_FILL_001',
                'device_type' => 'fill_line',
                'tank_id' => $mainTank1->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'g4h45g6hryh46t'),
            ],
            [
                'device_id' => 'SIM_FILL_002',
                'device_type' => 'fill_line',
                'tank_id' => $mainTank2->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'fill_token_line_2'),
            ],

            // Dispense Line Devices (RUT956) - assigned to main tanks
            [
                'device_id' => 'SIM_DISPENSE_001',
                'device_type' => 'dispense_line',
                'tank_id' => $mainTank1->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'sdff43gfe43'),
            ],
            [
                'device_id' => 'SIM_DISPENSE_002',
                'device_type' => 'dispense_line',
                'tank_id' => $mainTank2->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'dispense_token_line_2'),
            ],

            // Mobile Units (FMC225) - assigned to fuel tankers and browser tanks
            [
                'device_id' => 'SIM_MOBILE_001',
                'device_type' => 'mobile_unit',
                'tank_id' => $fuelTanker1->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'sfdgsdfefad'),
            ],
            [
                'device_id' => 'SIM_MOBILE_002',
                'device_type' => 'mobile_unit',
                'tank_id' => $fuelTanker2->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'mobile_token_ft_002'),
            ],
            [
                'device_id' => 'SIM_MOBILE_003',
                'device_type' => 'mobile_unit',
                'tank_id' => $fuelTanker3->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'mobile_token_ft_003'),
            ],
            [
                'device_id' => 'SIM_MOBILE_004',
                'device_type' => 'mobile_unit',
                'tank_id' => $browserTank1->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'mobile_token_bt_001'),
            ],
            [
                'device_id' => 'SIM_MOBILE_005',
                'device_type' => 'mobile_unit',
                'tank_id' => $browserTank2->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'mobile_token_bt_002'),
            ],
            [
                'device_id' => 'SIM_MOBILE_006',
                'device_type' => 'mobile_unit',
                'tank_id' => $browserTank3->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'mobile_token_bt_003'),
            ],
            [
                'device_id' => 'SIM_MOBILE_007',
                'device_type' => 'mobile_unit',
                'tank_id' => $browserTank4->tank_id,
                'status' => 'active',
                'api_token_hash' => hash('sha256', 'mobile_token_bt_004'),
            ],
        ];

        foreach ($devices as $dev) {
            HardwareDevice::updateOrCreate(
                ['device_id' => $dev['device_id']],
                array_merge($dev, ['site_id' => $site->id, 'last_seen' => now()])
            );
        }

        $this->command->info('✓ Created '.count($devices).' hardware devices (all assigned to registered tanks)');

        // 3. Create RFID Tags
        $rfidTags = [
            [
                'tag_id' => 'RFID_FT_001',
                'tank_id' => $fuelTanker1->tank_id,
                'sector' => 'Sector 1 - East Mining',
                'status' => 'active',
            ],
            [
                'tag_id' => 'RFID_FT_002',
                'tank_id' => $fuelTanker2->tank_id,
                'sector' => 'Sector 2 - West Mining',
                'status' => 'active',
            ],
            [
                'tag_id' => 'RFID_FT_003',
                'tank_id' => $fuelTanker3->tank_id,
                'sector' => 'Sector 3 - North Mining',
                'status' => 'active',
            ],
            [
                'tag_id' => 'RFID_BT_001',
                'tank_id' => $browserTank1->tank_id,
                'sector' => 'Mining Area A',
                'status' => 'active',
            ],
            [
                'tag_id' => 'RFID_BT_002',
                'tank_id' => $browserTank2->tank_id,
                'sector' => 'Mining Area B',
                'status' => 'active',
            ],
            [
                'tag_id' => 'RFID_BT_003',
                'tank_id' => $browserTank3->tank_id,
                'sector' => 'Mining Area C',
                'status' => 'active',
            ],
            [
                'tag_id' => 'RFID_BT_004',
                'tank_id' => $browserTank4->tank_id,
                'sector' => 'Mining Area D',
                'status' => 'active',
            ],
        ];

        foreach ($rfidTags as $tag) {
            RfidTag::updateOrCreate(
                ['tag_id' => $tag['tag_id']],
                $tag
            );
        }

        $this->command->info('✓ Created '.count($rfidTags).' RFID tags');

        // Update site's RFID list version to trigger device sync
        $site->update(['rfid_list_version' => $site->rfid_list_version + 1]);

        $this->command->info('✓ Hardware simulator data seeded successfully!');
        $this->command->newLine();
        $this->command->warn('Next steps:');
        $this->command->line('1. Start DummyHardSim: cd DummyHardSim && ./start.sh');
        $this->command->line('2. Open http://localhost:3030 in your browser');
        $this->command->line('3. Test all device endpoints');
    }
}
