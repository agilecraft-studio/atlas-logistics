<?php
/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		// Fleet Operations Dashboard: bust cached payloads on source-table writes.
		\App\Model\Trip::observe(\App\Observers\FleetDashboardObserver::class);
		\App\Model\TripExpense::observe(\App\Observers\FleetDashboardObserver::class);
		\App\Model\ComplianceDocument::observe(\App\Observers\FleetDashboardObserver::class);
		\App\Model\VehicleLogisticsProfile::observe(\App\Observers\FleetDashboardObserver::class);
		\App\Model\DriverComplianceProfile::observe(\App\Observers\FleetDashboardObserver::class);
		\App\Model\FuelModel::observe(\App\Observers\FleetDashboardObserver::class);
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		// Keep translations loading from resources/lang (Laravel 10 layout).
		$this->app->useLangPath(base_path('resources/lang'));

		// Passport::routes();
		$this->commands([
			InstallCommand::class,
			ClientCommand::class,
			KeysCommand::class,
		]);
	}
}
