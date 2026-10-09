<?php
/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
namespace App\Rules;

use App\Model\User;
use Illuminate\Contracts\Validation\Rule;

class UniqueContractNumber implements Rule {

	public function passes($attribute, $value) {
		if (\Request::get("edit") == "1") {
			$contract_no = User::meta()
				->where(function ($query) {
					$query->where('users_meta.key', '=', 'contract_number')
						->where('users_meta.value', '=', \Request::get('contract_number'))
						->where('users_meta.user_id', '!=', \Request::get('id'));
				})->exists();
			if (!$contract_no) {
				return true;
			} else {
				return false;
			}
		} else {
			$contract_no = User::meta()
				->where(function ($query) {
					$query->where('users_meta.key', '=', 'contract_number')
						->where('users_meta.value', '=', \Request::get('contract_number'));
				})->exists();
			if (!$contract_no) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function message() {
		return 'The :attribute must be unique.';
	}
}
