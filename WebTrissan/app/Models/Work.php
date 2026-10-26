<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Work extends Model
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
            'is_featured' => 'boolean',
            'gallery_images' => 'array',
            'highlight_tags' => 'array',
            'quality_standards' => 'array',
            'tolerance_value' => 'float',
            'scale_value' => 'integer',
            'production_date' => 'date',
        ];
    }

    /**
     * Get the resolved public URL of the card image, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->work_image));
    }

    /**
     * Get the card image followed by the gallery images as public URLs, without duplicates or missing files.
     *
     * @return Attribute<array<int, string>, never>
     */
    protected function galleryUrls(): Attribute
    {
        return Attribute::get(function (): array {
            $urls = array_map(
                fn (mixed $path): ?string => is_string($path) ? MediaUrl::resolve($path) : null,
                [$this->work_image, ...($this->gallery_images ?? [])]
            );

            return array_slice(array_values(array_unique(array_filter($urls))), 0, 3);
        });
    }

    /**
     * Get the production date formatted for Indonesian readers, e.g. "Oktober 2024".
     *
     * @return Attribute<string|null, never>
     */
    protected function productionDateLabel(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->production_date?->locale('id')->translatedFormat('F Y'));
    }

    /**
     * Get the full description as plain text, since the CMS stores it as rich text.
     *
     * @return Attribute<string, never>
     */
    protected function descriptionPlain(): Attribute
    {
        return Attribute::get(function (): string {
            $text = $this->full_description ?: ($this->short_description ?? '');
            $text = preg_replace('#</p>|<br\s*/?>|</li>#i', "\n", (string) $text);

            return trim(html_entity_decode(strip_tags((string) $text)));
        });
    }

    /**
     * Get the service that owns this work.
     *
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get product certifications related to this work.
     *
     * @return HasMany<ProductCertification, $this>
     */
    public function productCertifications(): HasMany
    {
        return $this->hasMany(ProductCertification::class);
    }
}
