<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the resolved public URL of the project image, or null when unset or missing.
     *
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => MediaUrl::resolve($this->image));
    }

    /**
     * Get the category that this portfolio item belongs to.
     *
     * @return BelongsTo<PortfolioCategory, $this>
     */
    public function portfolioCategory(): BelongsTo
    {
        return $this->belongsTo(PortfolioCategory::class);
    }
}
