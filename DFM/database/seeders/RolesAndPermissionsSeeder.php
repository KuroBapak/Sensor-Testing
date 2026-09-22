<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['is_system' => true]
        );

        // 2. Admin
        $admin = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['is_system' => false]
        );
        $adminExcluded = [
            'users.manage',
            'roles.manage',
            'sensitivity.manage',
            'backup.manage',
            'settings.manage',
        ];
        $adminPermissions = array_values(array_diff(Permissions::all(), $adminExcluded));
        $admin->syncPermissions($adminPermissions);

        // 3. Operator
        $operator = Role::firstOrCreate(
            ['name' => 'Operator'],
            ['is_system' => false]
        );
        $operatorPermissions = [
            'main_tank.view',
            'mobile_tanks.view',
            'alarms.view',
            'map.view',
            'vendor_fill.create',
        ];
        $operator->syncPermissions($operatorPermissions);

        // 4. Viewer
        $viewer = Role::firstOrCreate(
            ['name' => 'Viewer'],
            ['is_system' => false]
        );
        $viewerPermissions = [
            'main_tank.view',
            'mobile_tanks.view',
            'alarms.view',
            'map.view',
        ];
        $viewer->syncPermissions($viewerPermissions);

        // Seed default Super Admin user if none exists
        if (! User::where('email', 'admin@trissan.co.id')->exists()) {
            User::create([
                'name' => 'Super Administrator',
                'email' => 'admin@trissan.co.id',
                'password' => Hash::make('password'),
                'role_id' => $superAdmin->id,
            ]);
        }
    }
}
