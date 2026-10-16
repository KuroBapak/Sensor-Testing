<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnel';

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'qualification_rank' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the specialty cluster that this personnel belongs to.
     *
     * @return BelongsTo<SpecialtyCluster, $this>
     */
    public function specialtyCluster(): BelongsTo
    {
        return $this->belongsTo(SpecialtyCluster::class);
    }

    /**
     * Get assigned skills for this personnel.
     *
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'personnel_skill');
    }
}
