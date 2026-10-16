<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get personnel possessing this skill.
     *
     * @return BelongsToMany<Personnel, $this>
     */
    public function personnel(): BelongsToMany
    {
        return $this->belongsToMany(Personnel::class, 'personnel_skill');
    }
}
