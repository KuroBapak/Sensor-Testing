<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tank extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'tank_id';

    protected $guarded = [];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function hardwareDevices()
    {
        return $this->hasMany(HardwareDevice::class, 'tank_id', 'tank_id');
    }
}
