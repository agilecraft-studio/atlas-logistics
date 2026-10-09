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

class ResetPasswordController extends Controller {
	use ResetsPasswords;
	protected $redirectTo = 'admin/';
	public function __construct() {
		App::setLocale(AtlasLogistics::get('language'));
		$this->middleware('guest');
	}
}
