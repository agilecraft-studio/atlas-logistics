<?php
/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
namespace app\Http\Request;

use app\Helpers\Reply;
use Illuminate\Foundation\Http\FormRequest;

class CoreRequest extends FormRequest {

	protected function formatErrors(\Illuminate\Contracts\Validation\Validator $validator) {
		return Reply::formErrors($validator);
	}

}
