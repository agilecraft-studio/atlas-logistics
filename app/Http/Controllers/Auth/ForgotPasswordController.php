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
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller {
	use SendsPasswordResetEmails;
	public function __construct() {
		App::setLocale(AtlasLogistics::get('language'));
		$this->middleware('guest');
	}
}
