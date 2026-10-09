<?php
/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

*/

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PasswordResetModel extends Model {
	protected $table = "password_resets";
	protected $fillable = ['email', 'token', 'created_at'];
	public $timestamps = false;
}
