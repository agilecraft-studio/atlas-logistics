# Atlas Logistics — Fleet UI Convention (single shell)

Status 2026-10-11: Wave 1 SHIPPED (partial mission). Shell + nav + assets +
components + 8 Wave-1 views migrated. Waves 2-4 + legacy skin deletion PENDING.

## Layout contract — `resources/views/layouts/fleet.blade.php`
ONLY admin layout. Yields: `fleet_title`, `fleet_subtitle`,
`fleet_topbar_actions`, `content`, `styles`, `scripts`.
Shell = sidebar (from `config/fleet-nav.php`) + topbar (title/subtitle,
user chip with first Spatie role, CSRF logout POST to `route('logout')`,
bell linking to `logistics.compliance` with REAL cached alert count from
FleetDashboardService month payload; bell without badge if service fails).
Mobile: <=1000px icon rail, <=650px hidden sidebar + toggle; JS in fleet.js.

## Nav registry — `config/fleet-nav.php`
Sections[] => [label, items[] => [label, icon(bi-*), route(name),
permission(Spatie string), active(route pattern), params?, children?[]]].
Sidebar hides items unless `@can` passes; active via `routeIs`.
NOTE: `settings` route name + `addresses.index` + `Transactions list`
permission NOT verified to exist — sidebar guards with `Route::has` for
missing routes but `@can` on a nonexistent permission hides the item.
Verify before relying on those entries.

## Components — `resources/views/components/fleet/`
card, panel(title/icon), kpi(label/value/icon/color), table, status-pill,
toolbar, form-field, modal, alert, empty, pagination. Usage: `<x-fleet.panel
title=".." icon="bi-..">`, `<x-fleet.status-pill status="..">`.

## Assets — `public/assets/fleet/`
fleet.css (verbatim approved shell CSS + primitives: fleet-btn variants,
fleet-form, fleet-toolbar, fleet-modal, fleet-alert-box, fleet-pagination,
fleet-empty, fleet-pill, DataTables override), fleet.js (sidebar toggle,
fleet-modal close, data-fleet-confirm deletes, ApexCharts from JSON island),
vendor/ (local apexcharts.min.js + bootstrap-icons, NO CDN).

## Wave status
Wave 1 DONE: logistics/crud/index, logistics/crud/form,
logistics/registers/list, logistics/trips/show (layout+alerts+panel only;
inner cards/tables/modals still legacy markup), logistics/expenses/index,
logistics/advances/index, logistics/reports/profitability,
logistics/compliance/dashboard, fleet/dashboard/index (shell promoted).
DataTables policy: shared/legacy index tables render plain fleet tables
(no DataTables JS); trips/show retains inline $ legacy scripts untouched.
Waves 2-4 TODO: ~100 views still extend layouts.attex; legacy skins
(layouts/attex*, app, master, menu partials, legacy asset dirs) NOT deleted.
