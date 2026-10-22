<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSetting extends Model
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
            'hero_slides' => 'array',
            'trust_card' => 'array',
            'about_teaser' => 'array',
            'stats' => 'array',
            'hero_badge_card' => 'array',
            'corporate_highlight_cards' => 'array',
            'extra_data' => 'array',
        ];
    }

    /**
     * Retrieve page settings for a specific page slug.
     */
    public static function forPage(string $page): ?self
    {
        return static::where('page', $page)->first();
    }
}
