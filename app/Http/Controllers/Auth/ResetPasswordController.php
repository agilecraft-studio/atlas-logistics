<?php
/*
@copyright
Fleet Manager v7.0
Copyright (C) 2017-2025 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>
 */
namespace App\Http\Controllers\Auth;
use App;
use App\Http\Controllers\Controller;
use App\Model\AtlasLogistics;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller {
	use ResetsPasswords;
	protected $redirectTo = 'admin/';
	public function __construct() {
		App::setLocale('English-en');
		$this->middleware('guest');
	}

	public function showResetForm(Request $request, $token = null) {
		return view('auth.passwords.reset-attex')->with(['token' => $token, 'email' => $request->email]);
	}
}
