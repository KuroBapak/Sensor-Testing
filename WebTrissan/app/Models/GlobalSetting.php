<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'footer_legal_links' => 'array',
            'company_stats' => 'array',
            'phones' => 'array',
            'social_media' => 'array',
        ];
    }

    /**
     * Get the singleton instance.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'company_name' => 'PT. Apex Industrial Engineering',
            'company_tagline' => 'Industrial Engineering',
        ]);
    }
}
