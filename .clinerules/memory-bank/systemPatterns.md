# Atlas Logistics - System Patterns

## Base
Atlas Logistics Fleet Manager v6.1 on Laravel 10, upgrading to 13.
Source split: framework dir is the Laravel app; top-level assets/uploads/index.php is web-root content.
Prod dir /var/www/atlas-logistic mirrors framework.

## Data
Eloquent models in app/Model. Meta tables users_meta/vehicles_meta/bookings_meta via kodeine package (to replace with internal trait).
Views use laravelcollective Form/Html in ~138 files (to replace with plain Blade).

## Auth
Spatie Permission roles. Passport API tokens. DB-driven RBAC only.

## Notifications
Legacy edujugon/sly push packages are abandoned: replace with WebPush channel plus planned WhatsApp API service. No Slack.

## Install
Web installer imports MySQL dump; no migrations dir. PG schema in docs/fleet-pgsql.sql (65 tables, FKs deferred).

## Front-end shell & script loading

- Admin GUI is the **Attex** admin theme (coderthemes). Assets: Vite 5 builds (`resources/js/app.js`, `resources/js/layout.js`, `resources/js/head.js`) emit `app-*.js` / `layout-*.js` / `head-*.js`.
- **Classic (non-module) stack** (`resources/views/layouts/attex/legacy-scripts.blade.php`) loads BEFORE deferred Vite modules and BEFORE legacy inline scripts:
  1. `plugins-jquery.min.js` (jQuery 3.3.1) + `moment.js`
  2. `legacy-compat.js` **inlined** — Bootstrap 4→5 shim that (a) polyfills legacy `data-toggle/target/dismiss` → `data-bs-*`, (b) installs a jQuery plugin bridge (`modal`/`tab`/`tooltip`/… → `jQuery.fn.plugin`) against `window.bootstrap`, resolving `window.bootstrap.Plugin.getOrCreateInstance()` lazily via `setTimeout` poll if Bootstrap is not ready at parse time, (c) observes `MutationObserver` for dynamically injected markup.
  3. DataTable / Select2 / datepickers / FullCalendar / Chart.js / Fastclick / Pnotify stacks.
- Legacy views emit inline scripts in their `@section('script')`/`@section('script-bottom')`. These evaluate at **parse time** and need the synchronous global `window.jQuery` — hence the classic jQuery must precede `@yield('script')` in `footer-script.blade.php` (do not reorder after `@yield('script')`, or inline scripts run before jQuery).
- `resources/js/head.js` must NOT re-bind `window.$`/`window.jQuery` (module-level `import $ from 'jquery'`), or two jQuery instances coexist. App code reads the CDN global `jQuery` / `window.bootstrap`.
- Do not turn this app into a taxi/passenger-booking system; keep the Attex shell, controlled statuses/commodities, and configurable reference data.
