# Atlas Logistics — Fleet UI Convention (single shell)

> ## ⚠️ SKIN DECISION 2026-10-12 — SUPERSEDES the 2026-10-11 "fleet is the shell" text below.
> **Attex is now the ONE structural shell for the whole admin** (its
> mobile/tablet/web responsive behaviour is approved and proven). The fleet
> design contributes **COLORS ONLY**, via `tokens.css` + one override sheet.
> The fleet dashboard keeps its CONTENT widgets but renders inside the Attex
> shell; `layouts.fleet` / `layouts.fleet-legacy` are RETIRED (deleted).
> Attex core CSS/JS is NEVER edited — theming is override-only.
>
> **Every admin view now `@extends('layouts.attex')`; `grep -RIn
> "@extends('layouts.fleet')" resources/views` is EMPTY.** The sections below
> (fleet.css split, nav registry, compat layer, null-safety, title contract)
> remain valid for CONTENT — read them with this banner in mind.

### Load-order contract (head-css.blade.php — NEVER reorder)
`legacy-css` → **tokens.css** → Attex CSS (`@vite` app.scss/icons.scss) →
**attex-brand-theme.css** → **fleet-components.css** → per-view `@yield('css')`.
`fleet-charts.js` loads in the dashboard view after its content; `fleet.js`
(confirm + x-fleet.modal close) loads from the Attex footer.

### Files (`public/assets/fleet/`)
- `tokens.css` — SINGLE SOURCE OF TRUTH for the logo palette (navy/amber/
  neutral/semantic + `--chart-1..6`). Frozen. Legacy alias bridge lives here:
  `--atlas-navy-2`, `--atlas-teal`→amber, `--atlas-green`→success,
  `--atlas-blue`→info so old var names keep resolving.
- `attex-brand-theme.css` — OVERRIDE-ONLY recolor of Attex. Colors/borders/
  backgrounds/box-shadows ONLY. No display/grid/width/height/position/float/
  layout margin-padding/`@media`. `!important` used ONLY on the two DataTables
  `.paginate_button.current` rules (legacy BS4 renderer marks active only via
  `.current`; proven necessary, carried from fleet.css). Sidebar is dark →
  ACTIVE nav = navy-700 fill + 3px inset amber bar (not the amber-pill variant).
- `fleet-components.css` — CONTENT components split out of the old fleet.css
  (KPI grid/cards/panels/tables/pills/progress/alerts/availability/cost rows +
  legacy-markup compat). Content media queries (1400/1000/650px) live here —
  they are content, not shell.
- `fleet-charts.js` — chart bootstrap; colors read from tokens via
  `getComputedStyle` (no hardcoded hex). `fleet.js` no longer contains sidebar
  toggle (Attex owns it).

### Removed with the fleet shell
`.fleet-shell`, `.fleet-sidebar`, `.fleet-topbar`, `.fleet-menu-button`,
`.fleet-user`, `.fleet-notification`, `.fleet-content`, `.fleet-region`,
`.fleet-dashboard` negative-margin hack, 1000px icon-rail / 650px sidebar-hide
shell media queries, and the sidebar-toggle JS — Attex provides all of these.
Attex uses image logos (`.logo-lg`/`.logo-sm` `<img>`), so there is NO brand
text chip to recolor (audit finding — nothing to remap there).

---

# (Superseded 2026-10-11 record — kept for migration context)

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
