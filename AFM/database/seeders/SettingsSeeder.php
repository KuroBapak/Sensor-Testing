<?php

namespace Database\Seeders;

use App\Services\SettingsService;
use Illuminate\Database\Seeder;

/** Default global settings (section 4.5). Business settings live in the database, not in .env. */
class SettingsSeeder extends Seeder
{
    public function run(SettingsService $settings): void
    {
        $settings->update(SettingsService::DEFAULTS);
    }
}
