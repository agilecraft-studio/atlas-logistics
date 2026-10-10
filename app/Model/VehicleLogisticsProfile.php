<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VehicleLogisticsProfile extends Model
{
    protected $table = 'vehicle_logistics_profiles';
    protected $guarded = ['id'];
    protected $casts = ['next_service_date' => 'date'];

    public function vehicle()
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id');
    }
}
