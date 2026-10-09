<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaravelWebInstallerRequest extends FormRequest {

	public function authorize() {
		return true;
	}

	public function rules() {
		return [
			'purchase_code' => 'required',
			'hostname' => 'required',
			'username' => 'required',
			'database' => 'required',
		];
	}
}
