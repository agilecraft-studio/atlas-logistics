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

## Null-safety convention (added 2026-10-11 evening)
A nullable Eloquent relation read in a Blade or in a DataTables column closure is
a 500 the moment the FK is null or orphaned. Production proof:
$vehicle->types->displayname in vehicles/view_event.blade.php 500'd
/admin/vehicle/event/{id} for every vehicle whose type_id IS NULL (log
laravel-2026-10-11 17:03:50 + 17:05:44, userId 1).

Rules:
- Read relations with PHP 8 nullsafe ?-> plus a placeholder:
  $x->rel?->col ?? '—'. NEVER @-suppress. ?? alone is NOT enough: it only
  guards the FINAL segment, so $r->vehicle->types['displayname'] ?? em-dash
  still 500s when $r->vehicle itself is null.
- Placeholder conventions: person/assignee -> 'Unassigned'; entity (vehicle,
  vehicle type, customer, vendor) -> '—' (em dash). Numeric arithmetic that can
  receive null -> coalesce to 0, never to a placeholder.
- Legacy serialized metas: guard unserialize(). unserialize(null) raises a
  PHP 8.5 deprecation that Laravel escalates to ErrorException, i.e. a SECOND
  500 waiting on the same endpoint. Use:
  is_string($m) && $m !== '' ? unserialize($m) : null
- When a view reads the same nullable chain many times (bookings/list-actions
  read $row->vehicle->types->vehicletype 16x), compute ONE guarded local plus a
  small fare closure in an @php block; do not repeat the chain. Re-check the
  surrounding @if/@elseif/@endif brace balance after restructuring.
- Controllers: eager-load the relations the Blade actually reads, and 404 via
  abort(404) rather than handing a null model to a view that dereferences it.

## Title section contract (added 2026-10-11)
resources/views/layouts/fleet.blade.php resolves BOTH the <title> and the
topbar <h1>/<p> from ONE fallback chain, computed once in an @php block at the
top of <head>:
1. fleet_title / fleet_subtitle (wave-1 logistics views declare these)
2. page_title / page_subtitle (legacy attex naming, still honoured)
3. route-derived: current route name minus a trailing CRUD verb, title-cased
   (vehicles.index -> Vehicles, logistics.trips.index -> Trips). Read via
   request()->route(), which is populated during a real request.
4. 'Dashboard' / 'Atlas Logistics operations'

Do not @yield a title directly in this layout again. New views should still
declare @section('fleet_title', ...) for a human label; the route-derived step
exists only so the ~137 mechanically migrated legacy views (which declare no
title section at all) stop reading 'Dashboard' on every page. Legacy views are
NOT mass-edited for titles - the layout fallback is the fix.
