<?php

/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
namespace App\Http\Controllers\api1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as Login;

class Auth extends Controller {
	public function login(Request $request) {
		$email = $request->get("email");
		$password = $request->get("password");
		$res['status'] = "success";
		if (Login::attempt(['email' => $email, 'password' => $password])) {

			$res['api_token'] = Login::user()->api_token;

		} else {

			$res['status'] = "failed";
		}
		return response()->json($res);
	}

}
