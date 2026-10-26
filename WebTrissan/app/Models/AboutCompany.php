<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Get the resolved public URL of the hero image, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function heroImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->hero_image));
    }

    /**
     * Get the resolved public URL of the partners banner, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function partnersImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->partners_image));
    }

    /**
     * Get the resolved public URL of the company profile PDF, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function companyProfilePdfUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->company_profile_pdf));
    }

    /**
     * Get the hero description as plain-text paragraphs, since the CMS may store it as rich text.
     *
     * @return Attribute<array<int, string>, never>
     */
    protected function heroParagraphs(): Attribute
    {
        return Attribute::get(function (): array {
            $text = preg_replace('#</p>|<br\s*/?>#i', "\n", (string) $this->hero_description);
            $lines = array_map('trim', explode("\n", html_entity_decode(strip_tags((string) $text))));

            return array_values(array_filter($lines, fn (string $line): bool => $line !== ''));
        });
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
