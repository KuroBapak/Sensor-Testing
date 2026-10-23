<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

/** Placeholder vehicle types; replace them with the site's real list through the Excel import. */
class VehicleTypeSeeder extends Seeder
{
    /** name => [max capacity L, quota L per reset period] */
    public const TYPES = [
        'Daihatsu Gran Max' => [45, 20],
        'Toyota Hilux' => [80, 40],
        'Mitsubishi L300' => [63, 30],
        'Isuzu Elf' => [100, 50],
        'Hino Dutro' => [100, 60],
        'Mitsubishi Fuso Dump Truck' => [200, 120],
        'Komatsu PC200 Excavator' => [400, 250],
        'Caterpillar 320D Excavator' => [410, 260],
    ];

    public function run(): void
    {
        foreach (self::TYPES as $name => [$capacity, $quota]) {
            VehicleType::query()->updateOrCreate(
                ['name' => $name],
                ['max_capacity_liters' => $capacity, 'quota_liters' => $quota],
            );
        }
    }
}
