<?php

namespace App\Models;

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
