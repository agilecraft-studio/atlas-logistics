<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DriverVehicleModel extends Pivot {

	protected $table = "driver_vehicle";
	protected $fillable = ['driver_id', 'vehicle_id'];
	public $incrementing = true;
	public $timestamps = true;
	public function vehicle() {
		return $this->hasOne("App\Model\VehicleModel", "id", "vehicle_id")->withTrashed();
	}

	public function assigned_driver() {
		return $this->hasOne("App\Model\User", "id", "driver_id")->withTrashed();
	}
}
