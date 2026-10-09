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

class ReviewModel extends Model {
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $table = 'reviews';
	protected $fillable = ['user_id', 'booking_id', 'driver_id', 'ratings', 'review_text'];

	public function user() {
		return $this->hasOne("App\Model\User", "id", "user_id")->withTrashed();
	}

}
