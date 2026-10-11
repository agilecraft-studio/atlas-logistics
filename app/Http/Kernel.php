<?php
/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {
	protected $middleware = [
		\Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
		\Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
		\App\Http\Middleware\TrimStrings::class,
		\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
	];

	protected $middlewareGroups = [
		'web' => [
			\App\Http\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\Illuminate\Session\Middleware\StartSession::class,
			// \Illuminate\Session\Middleware\AuthenticateSession::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\VerifyCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
			\App\Http\Middleware\SecurityHeaders::class,

		],

		'api' => [
			'throttle:60,1',
			'bindings',
		],
	];

	protected $routeMiddleware = [
		'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
		'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
		'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
		'can' => \Illuminate\Auth\Middleware\Authorize::class,
		'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
		'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
		'superadmin' => \App\Http\Middleware\SuperAdmin::class,
		'internal.staff' => \App\Http\Middleware\InternalStaff::class,
		'officeadmin' => \App\Http\Middleware\OfficeAdmin::class,
		'lang_check' => \App\Http\Middleware\SetLocale::class,
		'userpermission' => \App\Http\Middleware\UserPermission::class,
		'backendpermission' => \App\Http\Middleware\BackendUserPermission::class,
		'updatepassporttoken' => \App\Http\Middleware\UpdatePassportToken::class,
		'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
		'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
		'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
		'auth_user' => \App\Http\Middleware\AuthUser::class,
		'lang_check_user' => \App\Http\Middleware\SetLocaleUser::class,
		'front_enable' => \App\Http\Middleware\CheckFrontEnable::class,
	];
}
