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

class PartsUsedModel extends Model {
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $table = "parts_used";
	protected $fillable = ['part_id', 'work_id', 'qty', 'price', 'total'];

	public function part() {
		return $this->hasOne("App\Model\PartsModel", "id", "part_id")->withTrashed();
	}

	public function workorder() {
		return $this->hasOne("App\Model\WorkOrders", "id", "work_id")->withTrashed();
	}
}
