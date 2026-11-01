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

class Customers extends FormRequest {
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
		// On create there is no "id" input; appending an empty ignore value
		// produces `unique:users,email,` which PostgreSQL rejects with
		// SQLSTATE[22P02] (invalid input syntax for type integer: "").
		// Only ignore the current record when editing.
		$ignore = \Request::get("id");
		return [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => $ignore ? 'unique:users,email,' . $ignore : 'unique:users,email',
			'phone' => 'required|numeric|digits_between:7,15',
			'gender' => 'required',
			'address' => 'required',

		];
	}
	public function messages() {
		return [
			// 'email.required' => 'email must be required',
			'email.unique' => 'email already taken',

		];
	}
}
