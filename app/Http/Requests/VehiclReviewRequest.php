<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehiclReviewRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'vehicle_id' => 'required',
			'reg_no' => 'required',
			'kms_out' => 'required',
			'kms_in' => 'required|gt:kms_out',
			'datetime_out' => 'required',
			'datetime_in' => 'required|different:datetime_out',
			'image' => 'nullable|mimes:jpg,png,jpeg|max:5120',
		];
	}
	public function messages() {
		return [
			'kms_in.gt' => 'Meter Reading Incoming must be greater than Meter Reading Outgoing',
		];
	}
}
