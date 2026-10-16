<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutCompany extends Model
{
    use HasFactory;

    protected $table = 'about_company';

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image_overlay' => 'array',
            'vision_mission_values' => 'array',
            'facilities' => 'array',
            'milestones' => 'array',
            'org_chart' => 'array',
        ];
    }

    /**
     * Get the singleton instance.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'hero_title' => 'PT Apex Industrial Engineering',
        ]);
    }
}
