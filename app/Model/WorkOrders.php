<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrders extends Model {
	use SoftDeletes;
	use HasFactory;
	protected $dates = ['deleted_at'];
	protected $casts = ['required_by' => 'date', 'created_on' => 'date', 'completed_on' => 'date'];
	protected $table = 'work_orders';
	protected $fillable = ['user_id', 'vehicle_id', 'trailer_id', 'vendor_id', 'mechanic_id', 'required_by', 'status', 'maintenance_type', 'description', 'meter', 'note', 'reference', 'price', 'completed_on', 'resolution'];

	public function vehicle() {
		return $this->belongsTo("App\Model\VehicleModel", "vehicle_id", "id")->withTrashed();
	}

	public function trailer() {
		return $this->belongsTo(Trailer::class, 'trailer_id')->withTrashed();
	}

	public function getMaintenanceTypeLabelAttribute(): string
	{
		return ucfirst(str_replace('_', ' ', $this->maintenance_type ?: 'repair'));
	}

	public function vendor() {
		return $this->belongsTo("App\Model\Vendor", "vendor_id", "id")->withTrashed();
	}

	public function mechanic() {
		return $this->belongsTo("App\Model\Mechanic", "mechanic_id", "id")->withTrashed();
	}

	public function parts() {
		return $this->hasMany("App\Model\PartsUsedModel", "work_id", "id");
	}

}
