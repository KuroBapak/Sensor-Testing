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
     * Get the WhatsApp number in international digits (e.g. 6281234567890), or null when none is set.
     *
     * Uses the dedicated WhatsApp number field, falling back to the first phone marked as WhatsApp.
     */
    public function whatsappNumber(): ?string
    {
        $candidates = [$this->whatsapp_number];

        foreach ($this->phones ?? [] as $phone) {
            if ($phone['is_whatsapp'] ?? false) {
                $candidates[] = $phone['tel_url'] ?: ($phone['display_text'] ?? '');
            }
        }

        foreach ($candidates as $candidate) {
            $digits = preg_replace('/\D+/', '', (string) $candidate);

            if (filled($digits)) {
                return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
            }
        }

        return null;
    }

    /**
     * Get the singleton instance.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'company_name' => 'PT. Trissan',
            'company_tagline' => 'Always Present Better',
        ]);
    }
}
