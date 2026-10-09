<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelModel extends Model {
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $table = "fuel";
	protected $fillable = ['vehicle_id', 'user_id', 'start_meter', 'reference', 'provience', 'note', 'qty', 'fuel_from', 'cost_per_unit', 'complete', 'date', 'vendor_name', 'mileage_type'];

	public function vehicle_data() {

		return $this->belongsTo("App\Model\VehicleModel", "vehicle_id", "id")->withTrashed();
	}

}
