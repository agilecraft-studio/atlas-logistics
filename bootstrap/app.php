<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure()
    ->withProviders()
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Fleet Manager (Atlas Logistics) legacy route groups.
            // The root controller namespace replaces the legacy
            // RouteServiceProvider::$namespace = 'App\Http\Controllers'.
            $namespace = 'App\\Http\\Controllers';

            Route::middleware('web')
                ->namespace($namespace)
                ->group(base_path('routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($namespace)
                ->group(base_path('routes/api.php'));

            Route::prefix('api1')
                ->middleware('api')
                ->namespace($namespace)
                ->group(base_path('routes/api1.php'));

            // Public customer frontend routes disconnected (internal admin app;
            // controllers/views remain on disk for reference, no routes registered).
            // Route::prefix('frontend')
            //     ->middleware('throttle')
            //     ->namespace($namespace)
            //     ->group(base_path('routes/frontend.php'));

            Route::prefix('admin')
                ->middleware('web')
                ->namespace($namespace)
                ->group(base_path('routes/admin.php'));

            Route::prefix('backend')
                ->middleware('web')
                ->namespace($namespace)
                ->group(base_path('routes/backend.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // --- Global middleware (mirrors the legacy App\Http\Kernel) ---
        $middleware->use([
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\TrimStrings::class,
        ]);

        // PreventRequestsDuringMaintenance is registered by the framework already.

        // --- Middleware groups (mirrors the legacy Kernel groups) ---
        $middleware->group('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            'throttle:60,1',
            'bindings',
        ]);

        // --- Route middleware aliases (mirrors the legacy Kernel aliases) ---
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\SuperAdmin::class,
            'internal.staff' => \App\Http\Middleware\InternalStaff::class,
            'officeadmin' => \App\Http\Middleware\OfficeAdmin::class,
            'lang_check' => \App\Http\Middleware\SetLocale::class,
            'userpermission' => \App\Http\Middleware\UserPermission::class,
            'backendpermission' => \App\Http\Middleware\BackendUserPermission::class,
            'updatepassporttoken' => \App\Http\Middleware\UpdatePassportToken::class,
            'auth_user' => \App\Http\Middleware\AuthUser::class,
            'lang_check_user' => \App\Http\Middleware\SetLocaleUser::class,
            'front_enable' => \App\Http\Middleware\CheckFrontEnable::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


