<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest {

	public function authorize() {
		return (Auth::user());
	}

	public function rules() {
		return [
			'excel' => 'required|mimes:xlsx,xls',
		];
	}

	public function messages() {
		return [
			'excel.mimes' => 'File type must be Excel.',
		];
	}
}
