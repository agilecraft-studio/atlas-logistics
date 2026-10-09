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

class BookingIncome extends Model {

	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $table = "booking_income";
	protected $fillable = ['booking_id', 'income_id'];

	public function booking_income() {
		return $this->hasOne("App\Model\IncomeModel", "id", "income_id")->withTrashed();
	}

}
