<?php

namespace App\Console\Commands;

use Database\Seeders\TankSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:refresh-readings')]
#[Description('Regenerate the dummy tank readings so they end at the current hour (demo data only)')]
class RefreshDemoReadings extends Command
{
    public function handle(): int
    {
        TankSeeder::refresh();

        $this->info('Pembacaan tangki dibuat ulang hingga jam ini.');

        return self::SUCCESS;
    }
}
