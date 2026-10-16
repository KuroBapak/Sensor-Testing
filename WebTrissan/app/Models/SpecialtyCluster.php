<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecialtyCluster extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get personnel belonging to this specialty cluster.
     *
     * @return HasMany<Personnel, $this>
     */
    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class)->orderBy('sort_order');
    }
}
