<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidTag extends Model
{
    protected $primaryKey = 'tag_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_id', 'tank_id');
    }
}
