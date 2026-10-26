<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Get the resolved public URL of the navbar logo, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function navbarLogoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->navbar_logo));
    }

    /**
     * Get the resolved public URL of the footer logo, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function footerLogoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->footer_logo));
    }

    /**
     * Get the resolved public URL of the footer partner banner, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function footerPartnerImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->footer_partner_image));
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
