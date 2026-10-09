<?php
/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
namespace App\Providers;

use App\AtlasLogistics\CustomTransportManager;
use Illuminate\Mail\MailServiceProvider;

class CustomMailServiceProvider extends MailServiceProvider {

	protected function registerSwiftTransport() {
		$this->app['swift.transport'] = $this->app->share(function ($app) {
			return new CustomTransportManager($app);
		});
	}
}
