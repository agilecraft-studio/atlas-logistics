<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Http\Middleware;

use App;
use Auth;
use Closure;
use App\Model\AtlasLogistics;

class SetLocaleUser {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		if (Auth::user() && Auth::user()->user_type == "C") {
			App::setLocale(AtlasLogistics::frontend('language'));
		} else {
			App::setLocale(AtlasLogistics::frontend('language'));
		}
		return $next($request);
	}
}
