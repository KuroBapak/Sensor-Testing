<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Admin User for Filament Panel
        User::firstOrCreate(
            ['email' => 'admin@trissan.com'],
            [
                'name' => 'Admin Trissan',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Also keep test user if needed
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // CMS Seeders
        $this->call([
            GlobalSettingSeeder::class,
            PageSettingSeeder::class,
            AboutCompanySeeder::class,
            ServiceSeeder::class,
            WorkSeeder::class,
            CertificationSeeder::class,
            PortfolioSeeder::class,
            PersonnelSeeder::class,
        ]);
    }
}
