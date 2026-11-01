<?php

/*
 * Internal admin application - no public/customer pages.
 * "/" redirects into the admin area. Authentication lives under /admin.
 * NOTE: the legacy web installer, version-upgrade controllers and the public
 * payment checkout flow were hard removed; all installation/upgrade/migration
 * HTTP endpoints are gone. Database work runs via CLI migrations only
 * (`php artisan migrate --force`).
 */

Route::get('/', function () {
    return redirect('admin');
})->name('root');
