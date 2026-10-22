<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificationCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get corporate accreditations under this category.
     *
     * @return HasMany<CorporateAccreditation, $this>
     */
    public function corporateAccreditations(): HasMany
    {
        return $this->hasMany(CorporateAccreditation::class)->orderBy('sort_order');
    }
}
