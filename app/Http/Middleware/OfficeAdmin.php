<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Http\Middleware;

use Auth;
use Closure;

class OfficeAdmin {

	public function handle($request, Closure $next) {

		if (Auth::user()->user_type == "D" && Auth::user()->id != $request->get("id")) {
			return redirect("admin");
		}
		return $next($request);
	}
}
