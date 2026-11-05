<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VehicleTelematics extends Model
{
    protected $table = 'vehicle_telematics';
    protected $guarded = ['id'];
    protected $casts = [
        'recorded_at' => 'datetime',
        'ignition' => 'boolean',
        'raw' => 'array',
    ];

    public function vehicle()
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id')->withTrashed();
    }
}
