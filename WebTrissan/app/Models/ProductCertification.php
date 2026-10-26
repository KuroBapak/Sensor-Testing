<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCertification extends Model
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
            'validity_date' => 'date',
            'spec_rows' => 'array',
        ];
    }

    /**
     * Get the resolved public URL of the certificate image, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function certificateImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->certificate_image));
    }

    /**
     * Get the resolved public URL of the certificate PDF, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function certificatePdfUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->certificate_pdf));
    }

    /**
     * Get the validity date formatted for Indonesian readers, e.g. "24 Nov 2026".
     *
     * @return Attribute<string|null, never>
     */
    protected function validityLabel(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->validity_date?->locale('id')->translatedFormat('j M Y'));
    }

    /**
     * Get the work that this certification optionally belongs to.
     *
     * @return BelongsTo<Work, $this>
     */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }
}
