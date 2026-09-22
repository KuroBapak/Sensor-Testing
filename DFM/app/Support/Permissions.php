<?php

namespace App\Support;

class Permissions
{
    public const PAGES = [
        'main_tank.view' => 'View Main Tank',
        'mobile_tanks.view' => 'View Mobile Tanks',
        'alarms.view' => 'View Alarms',
        'map.view' => 'View Live Fleet Map',
    ];

    public const ALARMS = [
        'alarms.manage' => 'Manage & Resolve Alarms',
    ];

    public const FLEET = [
        'tanks.manage' => 'Manage Tanks',
        'hardware.manage' => 'Manage Hardware Devices',
        'rfid.manage' => 'Manage RFID Tags',
        'vendor_fill.create' => 'Record Vendor Fill',
    ];

    public const SYSTEM = [
        'settings.manage' => 'Manage Site Settings',
        'sensitivity.manage' => 'Manage Alert Sensitivity',
        'backup.manage' => 'Manage Backup & Storage',
        'geofences.manage' => 'Manage Geofences',
        'users.manage' => 'Manage Users',
        'roles.manage' => 'Manage Roles & Permissions',
    ];

    public const REPORTS = [
        'reports.export' => 'Export Reports to CSV',
    ];

    /**
     * @return array<string, array<string, string>>
     */
    public static function grouped(): array
    {
        return [
            'Pages' => self::PAGES,
            'Alarms' => self::ALARMS,
            'Fleet' => self::FLEET,
            'System' => self::SYSTEM,
            'Reports' => self::REPORTS,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_merge(
            array_keys(self::PAGES),
            array_keys(self::ALARMS),
            array_keys(self::FLEET),
            array_keys(self::SYSTEM),
            array_keys(self::REPORTS),
        );
    }
}
