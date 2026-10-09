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

class SetLocale {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		if (!$this->alreadyInstalled()) {
			return redirect('installation');
		}
		if (Auth::user()->getMeta('language') != null) {
			App::setLocale(Auth::user()->getMeta('language'));
		} else {
			App::setLocale(AtlasLogistics::get('language'));
		}
		return $next($request);
	}
	public function alreadyInstalled() {
		return (file_exists(storage_path('installed')) && file_get_contents(storage_path('installed')) == "version6.5");
	}
}
