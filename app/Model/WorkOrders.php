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

	public const STATUSES = [
		'pending_approval' => 'Pending Approval',
		'in_bay' => 'In Bay',
		'awaiting_parts' => 'Awaiting Parts',
		'completed' => 'Completed',
		'cancelled' => 'Cancelled',
	];

	/** Legacy values that may still exist in the table. */
	public const LEGACY_STATUSES = [
		'Pending' => 'pending_approval',
		'Processing' => 'in_bay',
		'Hold' => 'awaiting_parts',
		'Completed' => 'completed',
		'Cancelled' => 'cancelled',
	];

	public const MAINTENANCE_TYPES = [
		'repair' => 'Repair',
		'service' => 'Service',
		'inspection' => 'Inspection',
		'tyres' => 'Tyres',
		'bodywork' => 'Bodywork',
		'electrical' => 'Electrical',
		'other' => 'Other',
	];

	protected $dates = ['deleted_at'];
	protected $casts = ['required_by' => 'date', 'created_on' => 'date', 'completed_on' => 'date'];
	protected $table = 'work_orders';
	protected $fillable = ['user_id', 'vehicle_id', 'trailer_id', 'driver_id', 'vendor_id', 'mechanic_id', 'required_by', 'status', 'maintenance_type', 'description', 'meter', 'note', 'reference', 'price', 'completed_on', 'resolution'];

	public static function normalizeStatus(?string $status): string
	{
		$status = (string) $status;
		return self::LEGACY_STATUSES[$status] ?? $status;
	}

	public static function activeStatuses(): array
	{
		return ['pending_approval', 'in_bay', 'awaiting_parts', 'Pending', 'Processing', 'Hold'];
	}

	public static function historyStatuses(): array
	{
		return ['completed', 'cancelled', 'Completed', 'Cancelled'];
	}

	public function getStatusLabelAttribute(): string
	{
		$key = self::normalizeStatus($this->status);
		return self::STATUSES[$key] ?? ucfirst(str_replace('_', ' ', $key ?: 'pending_approval'));
	}

	public function getStatusKeyAttribute(): string
	{
		return self::normalizeStatus($this->status) ?: 'pending_approval';
	}

	public function getPartsTotalAttribute(): float
	{
		return (float) $this->parts->sum(fn ($used) => $used->restored_at ? 0 : (float) $used->total);
	}

	public function getTotalCostAttribute(): float
	{
		return (float) $this->price + $this->parts_total;
	}

	public function driver() {
		return $this->belongsTo("App\Model\User", "driver_id", "id")->withTrashed();
	}

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
