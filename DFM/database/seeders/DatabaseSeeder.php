<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            PlaceholderDataSeeder::class,
            SiteSeeder::class,
            HardwareSimulatorSeeder::class, // Must run before FuelMonitoringSeeder
            FuelMonitoringSeeder::class,
        ]);

        $superAdminRole = Role::where('name', 'Super Admin')->first();

        // Assign super admin role to test user if exists or create
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );
        $user->role_id = $superAdminRole?->id;
        $user->save();
    }
}
