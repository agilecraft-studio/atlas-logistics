<?php

/*

@copyright



Fleet Manager v6.5



Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.

Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>



 */

namespace App\Providers;



use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

use Illuminate\Support\Facades\Route;



class RouteServiceProvider extends ServiceProvider {

	/**

	 * This namespace is applied to your controller routes.

	 *

	 * In addition, it is set as the URL generator's root namespace.

	 *

	 * @var string

	 */

	protected $namespace = 'App\Http\Controllers';



	/**

	 * Define your route model bindings, pattern filters, etc.

	 *

	 * @return void

	 */

	public function boot() {

		//



		parent::boot();



	}



	/**

	 * Define the routes for the application.

	 *

	 * @return void

	 */

	public function map() {

		$this->mapApiRoutes();

		$this->mapWebRoutes();

		$this->mapFrontendRoutes();

		$this->mapAdminRoutes();

		$this->mapBackendRoutes();

		$this->mapApi1Routes();

	}



	/**

	 * Define the "web" routes for the application.

	 *

	 * These routes all receive session state, CSRF protection, etc.

	 *

	 * @return void

	 */

	protected function mapWebRoutes() {

		Route::middleware('web')

			->namespace($this->namespace)

			->group(base_path('routes/web.php'));

	}



	/**

	 * Define the "api" routes for the application.

	 *

	 * These routes are typically stateless.

	 *

	 * @return void

	 */



	protected function mapApiRoutes() {

		Route::prefix('api')

			->middleware('api')

			->namespace($this->namespace)

			->group(base_path('routes/api.php'));

	}

	protected function mapApi1Routes() {

		Route::prefix('api1')

			->middleware('api')

			->namespace($this->namespace)

			->group(base_path('routes/api1.php'));

	}

	protected function mapFrontendRoutes() {

		// Public customer frontend routes disconnected (internal admin app;
		// controllers/views remain on disk for reference, no routes registered).

	}

	protected function mapAdminRoutes() {

		Route::prefix('admin')

			->middleware('web')

			->namespace($this->namespace)

			->group(base_path('routes/admin.php'));

	}

	protected function mapBackendRoutes() {

		Route::prefix('backend')

			->middleware('web')

			->namespace($this->namespace)

			->group(base_path('routes/backend.php'));

	}

}

