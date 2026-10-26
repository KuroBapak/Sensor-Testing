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
        $this->seedUsers();

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

    /**
     * Create the admin user, and a demo user outside production-like environments.
     */
    private function seedUsers(): void
    {
        $isLocal = app()->environment('local', 'testing');
        $password = filled(config('cms.admin_password')) ? config('cms.admin_password') : ($isLocal ? 'password' : null);

        if ($password === null) {
            $this->command?->warn('CMS_ADMIN_PASSWORD is not set; skipping admin user creation.');
        } else {
            User::firstOrCreate(
                ['email' => config('cms.admin_emails')[0] ?? 'admin@trissan.com'],
                [
                    'name' => 'Admin Trissan',
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );
        }

        if ($isLocal) {
            User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
