# Atlas Logistics — Fleet UI Convention (single shell)

Status 2026-10-11 (PM): FULL MIGRATION SHIPPED. ALL admin views now extend
`layouts.fleet`; zero views reference `layouts.attex`/`layouts.app`.
Waves 2-4 collapsed into one mechanical migration + compat layer (see below).

## Layout contract — `resources/views/layouts/fleet.blade.php`
ONLY admin layout. Yields: `fleet_title`, `fleet_subtitle`,
`fleet_topbar_actions`, `content`, `styles`, `scripts`.
Shell = sidebar (from `config/fleet-nav.php`) + topbar (title/subtitle,
user chip with first Spatie role, CSRF logout POST to `route('logout')`,
bell linking to `logistics.compliance` with REAL cached alert count from
FleetDashboardService month payload; bell without badge if service fails).
Mobile: <=1000px icon rail, <=650px hidden sidebar + toggle; JS in fleet.js.
De-zoomed 2026-10-11: `.fleet-dashboard` no longer has negative margins
(padding 1.5rem desktop / .75rem mobile); shell font scale normalized for
full-page use (nav/table/label text 11–14.5px, h1 20px, KPI value 22px).

## Legacy runtime — `resources/views/layouts/fleet-legacy.blade.php`
Included by fleet.blade.php before `@yield('scripts')`. Provides, from LOCAL
assets only (no CDN): `assets/js/plugins-jquery.min.js` (jQuery 3.3.1 bundle,
the same file attex used) + `assets/js/moment.js`, then an inline shim with:
window.Laravel CSRF + $.ajaxSetup header, global route/label JS vars
(getDriverRoute, selectDriver, …), PNotify toast shim (fleet-styled),
$.fn.modal show/hide shim with show/hide.bs.modal events + relatedTarget and
delegated [data-toggle/data-bs-toggle=modal] + [data-bs-dismiss=modal] handling.
WHY: ~125 migrated views keep inline jQuery page scripts (PNotify in 36,
checkbox bulk-select in 25, dependent dropdowns in 5). Rewriting them all is
not done; they run against this runtime. DataTables/select2/validate are NOT
loaded (no view calls them directly; legacy index tables render as plain
fleet tables by policy).

## Nav registry — `config/fleet-nav.php`
Sections[] => [label, items[] => [label, icon(bi-*), route(name),
permission(Spatie string), active(route pattern), params?, children?[]]].
Sidebar hides items unless `@can` passes; active via `routeIs`.
NOTE: `settings` route name + `addresses.index` + `Transactions list`
permission NOT verified to exist — sidebar guards with `Route::has` for
missing routes but `@can` on a nonexistent permission hides the item.

## Components — `resources/views/components/fleet/`
card, panel(title/icon), kpi(label/value/icon/color), table, status-pill,
toolbar, form-field, modal, alert, empty, pagination. Usage: `<x-fleet.panel
title=".." icon="bi-..">`, `<x-fleet.status-pill status="..">`.
Wave-1 logistics views use components directly. The 137 mechanically migrated
legacy views use original Bootstrap markup, RESTYLED by the compat layer.

## Compat layer — bottom of `public/assets/fleet/fleet.css`
Styles legacy Bootstrap/AdminLTE classnames to the fleet design: grid
(row/col-md-*/col-sm-*/col-xs-6), card family, table family (incl.
thead-inverse), form-group/form-control/input-group, btn family (teal
primary), badge family, alert family, modal stack, nav-tabs/pills,
dropdown-menu, AdminLTE info-box, invoice-col, pure-CSS .switch/.slider
toggle, common utilities (text-*, d-flex, spacing ms-/mb-…), .fleet-toast
(PNotify shim), collapsed-sidebar rules. Extend THIS file (not a second CSS
framework) when a legacy view shows an unstyled pattern.

## Assets — `public/assets/fleet/`
fleet.css (shell + primitives + compat layer), fleet.js (sidebar toggle,
fleet-modal close, data-fleet-confirm deletes, ApexCharts from JSON island),
vendor/ (local apexcharts.min.js + bootstrap-icons, NO CDN).

## Migration ledger (2026-10-11)
Wave 1 (component-upgraded, hand-written): logistics/crud/index, crud/form,
registers/list, trips/show (inner cards/tables still legacy markup),
expenses/index ($m ordering bug fixed same day), advances/index,
reports/profitability, compliance/dashboard, fleet/dashboard/index.
Waves 2-4 (mechanical, 137 files): layouts.attex/app -> layouts.fleet;
page_title->fleet_title; extra_css->styles; script->scripts (8 dual-section
views merged); 133 breadcrumb sections removed; 397 fa->bi icon conversions;
markup kept + restyled via compat layer. Verified: view:cache clean; 51-URL
authenticated sweep 200+fleet-shell; /installation,/installed,/migrate,
/migration,/upgrade* all 404; zero new log entries on final sweep.

## Still pending / known limits
- Legacy skins remain ON DISK but unused: layouts/attex*, layouts/app,
  layouts/master, auth-attex, attex partials, legacy asset dirs. Deletion is
  a separate task (some legacy views still @include from attex partials? NO —
  none extend them anymore; safe to delete after a grep for @include).
- Full component markup upgrade (card->x-fleet.panel etc.) still pending for
  the 137 mechanically migrated views; compat layer keeps them consistent.
- bookings/, booking_quotation/, chat, track_driver, driver_maps, geocode are
  legacy taxi-era (routes mostly disconnected) — migrated mechanically only.
- A few migrated views carry their own external <script src> (Google Maps
  x4, Pusher, Summernote CDN, jQuery Validate CDN in attex-era partials) —
  pre-existing, untouched.
