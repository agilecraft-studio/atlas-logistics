<?php
/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
namespace App\Listeners;

use Artisan;

class LoginListener {

	public function __construct() {

	}

	public function handle($event) {

		// Artisan::call('notification:generate');
		Artisan::call('schedule:run');
	}
}
