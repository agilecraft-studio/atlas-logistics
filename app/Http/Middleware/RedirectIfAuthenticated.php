<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated {

	public function handle($request, Closure $next, $guard = null) {
		if (!$this->alreadyInstalled()) {
			return redirect("installation");
		}
		if (Auth::guard($guard)->check()) {
			return redirect('admin');
		}

		return $next($request);
	}

	public function alreadyInstalled() {
		return (file_exists(storage_path('installed')) && file_get_contents(storage_path('installed')) == "version6.5");
	}
}
