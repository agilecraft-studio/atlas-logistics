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

class WorkOrderRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		if (Auth::user()->user_type == "S" || Auth::user()->user_type == "O") {
			return true;
		} else {
			abort(404);
		}
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'vehicle_id' => 'required|integer',
			'trailer_id' => 'nullable|integer',
			'driver_id' => 'nullable|integer|exists:users,id',
			'vendor_id' => 'required|integer',
			'mechanic_id' => 'nullable|integer',
			'required_by' => 'required|date|date_format:Y-m-d',
			'price' => 'required|numeric|min:0',
			'status' => 'nullable|in:' . implode(',', array_keys(\App\Model\WorkOrders::STATUSES)),
			'maintenance_type' => 'nullable|in:' . implode(',', array_keys(\App\Model\WorkOrders::MAINTENANCE_TYPES)),
			'meter' => 'nullable|numeric|min:0',
			'completed_on' => 'nullable|date|date_format:Y-m-d',
			'parts' => 'nullable|array',
			'parts.*' => 'integer|min:1',
		];
	}
}
