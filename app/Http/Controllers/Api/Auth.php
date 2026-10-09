<?php
/*
@copyright
Fleet Manager v6.1
Copyright (C) 2017-2022 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Api;
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
