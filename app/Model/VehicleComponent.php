<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VehicleComponent extends Model
{
    protected $table = 'vehicle_components';
    protected $guarded = ['id'];
    protected $casts = ['fitted_date' => 'date', 'removed_date' => 'date'];

    public function getOwnerAttribute()
    {
        return $this->owner_type ? $this->owner_type . ':' . $this->owner_id : null;
    }
}
