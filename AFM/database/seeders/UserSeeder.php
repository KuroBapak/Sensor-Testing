<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo accounts: one Super Admin and one Admin. Super Admin accounts are not
 * managed in the app, so the first one comes from here. Change the demo
 * password before exposing the app outside the demo machine.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->account('superadmin@example.com', 'Super Admin', Role::SuperAdmin);
        $this->account('admin@example.com', 'Admin Lapangan', Role::Admin);
    }

    private function account(string $email, string $name, Role $role): void
    {
        User::query()->firstOrNew(['email' => $email])->forceFill([
            'name' => $name,
            'password' => 'password',
            'role' => $role,
            'email_verified_at' => now(),
        ])->save();
    }
}
