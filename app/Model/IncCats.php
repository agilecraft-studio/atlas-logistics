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

class IncCats extends Model {
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $fillable = ['name', 'user_id', 'cost', 'type'];
	protected $table = "income_cat";
	public function income() {
		return $this->hasMany("App\Model\IncomeModel", "income_cat", "id")->withTrashed();
	}
}
