<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Auth as Login;

class Testcontroller extends Controller {
	public function fetch() {
		return Auth::user();
	}
	public function login() {
		Login::loginUsingId(1);
		$user = Login::user();
		return view('sample');
	}
}
