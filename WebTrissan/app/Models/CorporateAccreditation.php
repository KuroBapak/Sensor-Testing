<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateAccreditation extends Model
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
            'validity_date' => 'date',
        ];
    }

    /**
     * Get the category that this corporate accreditation belongs to.
     *
     * @return BelongsTo<CertificationCategory, $this>
     */
    public function certificationCategory(): BelongsTo
    {
        return $this->belongsTo(CertificationCategory::class);
    }
}
