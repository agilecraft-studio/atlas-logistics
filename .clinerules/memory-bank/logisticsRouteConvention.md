# Atlas Logistics — Logistics Module Route & Naming Convention (THE decisive fact)

> READ THIS FIRST before touching anything under `routes/logistics.php`,
> `app/Http/Controllers/Admin/Logistics/`, `app/Model/Trip*`, or
> `resources/views/logistics/`. It settles every route name, param, permission
> and view-var question so you never have to re-read the controllers.

## 1. Route names are ALL `logistics.*`
- `routes/logistics.php` wraps everything in:
  `Route::prefix('logistics')->namespace('Logistics')->group(function () { ... });`
- **The group adds NO name prefix** (`->name()` is NOT on the group). Therefore
  EVERY route is named explicitly, e.g. `->name('logistics.trips.index')`.
- **NEVER** use the Phase-3 draft block verbatim: it writes `->name('trips.start')`,
  `->name('expenses.index')`, etc. Those names DO NOT EXIST and throw
  `RouteNotFoundException`. Always prefix with `logistics.`.
- URL prefix: `/admin/logistics/...` (the file is `require`d inside the
  authenticated `lang_check, auth, officeadmin, IsInstalled` group in routes/admin.php).

## 2. CRUD record param is `{id}` — NOT `{trip}`
- `CrudController::id()` returns `request()->route('id')`. So every logistics route
  that identifies one record MUST use `{id}`.
- `Route::resource(...)` is NOT used (it would create `{trip}` and unwanted
  `show`/`destroy`). CRUD is explicit routes: index, create, store, edit, update, destroy.

## 3. Authoritative Phase 3 route table (use these exact names)
```
logistics.trips.index            GET    /logistics/trips
logistics.trips.create           GET    /logistics/trips/create
logistics.trips.store            POST   /logistics/trips
logistics.trips.edit             GET    /logistics/trips/{id}/edit
logistics.trips.update           PUT    /logistics/trips/{id}
logistics.trips.destroy          DELETE /logistics/trips/{id}
logistics.trips.show             GET    /logistics/trips/{id}          (custom show())
logistics.trips.start            POST   /logistics/trips/{id}/start
logistics.trips.complete         POST   /logistics/trips/{id}/complete
logistics.trips.close            POST   /logistics/trips/{id}/close
logistics.trips.cancel           POST   /logistics/trips/{id}/cancel
logistics.trips.checkpoints.store     POST   /logistics/trips/{id}/checkpoints
logistics.trips.checkpoints.destroy   DELETE /logistics/trips/{id}/checkpoints/{rowId}
logistics.trips.incidents.store       POST   /logistics/trips/{id}/incidents
logistics.trips.incidents.destroy     DELETE /logistics/trips/{id}/incidents/{rowId}
logistics.expenses.index         GET    /logistics/expenses
logistics.expenses.store         POST   /logistics/trips/{tripId}/expenses
logistics.expenses.approve       PUT    /logistics/expenses/{id}/approve
logistics.expenses.reject        PUT    /logistics/expenses/{id}/reject
logistics.expenses.destroy       DELETE /logistics/expenses/{id}
logistics.advances.index         GET    /logistics/advances
logistics.advances.store         POST   /logistics/trips/{tripId}/advances
logistics.advances.destroy       DELETE /logistics/advances/{id}
logistics.reports.profitability  GET    /logistics/reports/profitability
```
Constrain numeric params: `->where(['id' => '[0-9]+', 'rowId' => '[0-9]+', 'tripId' => '[0-9]+'])`.

## 4. Permission module names & Blade gates
- `CrudController` constructor applies `permission:{$p} list|add|edit|delete` automatically
  from `protected string $permission`.
- Phase 3 permission module strings (must match migration 2026_10_11_000004 and Spatie DB):
  `Trips`, `TripExpenses`, `DriverAdvances`, `LogisticsReports` (report gate).
- Blade directives used in views: `@can('Trips edit')`, `@can('TripExpenses add')`,
  `@can('Advances delete')`. Use `@can` with `<Module> <action>` exactly as the
  Phase 2 blades do.

## 5. CrudController contract (what a subclass MUST implement)
- Abstract: `model()`, `fields()`, `columns()`. Missing `columns()` = fatal on index.
- Provide your OWN `show()` if you need a detail page (base class has none).
- Helpers available: `vehicleOptions()`, `trailerOptions()`, `driverOptions()`,
  `customerOptions()`, `countryOptions()`, `currencyOptions()`, `ownerOptions()`,
  `splitOwner()`. Field `rules` string uses `{id}` for unique-ignore; array rules allowed
  (regex with `|`) — form.blade.php handles both (str_contains guard fixed).
- Models: real vehicle model is `App\Model\VehicleModel` (NOT `App\Model\Vehicle`).
  Owner options return keys like `vehicle:12` / `trailer:3` / `driver:7`.

## 6. Views the base CRUD uses
- index → `resources/views/logistics/crud/index.blade.php`
- form  → `resources/views/logistics/crud/form.blade.php`
- Trips detail → `resources/views/logistics/trips/show.blade.php` (custom).
  `show()` MUST supply these vars to the view: `$trip`, `$summary` (array from
  TripFinanceService::summary: keys revenue, total_cost, profit, margin, pending,
  pending_count, advances, advance_balance), `$base` (currency code), `$m` (money formatter
  closure), `$open` (bool: trip is open/editable), `$borders`, `$categories`,
  `$countries`, `$currencyOptions` (closure fn($base)=>html options string).

## 7. Services / config keys
- `App\Services\TripFinanceService::summary($trip)` → the summary array above.
- `App\Services\CurrencyService` → `base()` (code, default MWK) and rate conversion.
- `config/logistics.php` keys: `base_currency`, `trip_statuses`, `trip_types`,
  `payment_methods`, `operational_statuses`, etc.

## 8. DB trip tables (already migrated in Phase 2)
`trips`, `trip_expenses`, `trip_expense_categories`, `driver_advances`,
`trip_checkpoints`, `trip_incidents`. No new migration needed for Phase 3 UI.

## 9. Phase-3 wiring status — DONE 2026-10-11 (verified)
- `routes/logistics.php`: Phase-3 block live (all 22 names from §3 registered;
  `route:list --name=logistics` shows 55 routes; all names resolve in tinker).
- `TripsController`: `use App\Model\Vehicle` → `VehicleModel` + `VehicleLogisticsProfile`;
  `vehicle_id` options via `$this->vehicleOptions()`; `columns()` + `rowLinks()` +
  `headerLinks()` + `canDelete()` (planned-only) added; custom `show()` passes
  `$trip/$summary/$blockers/$currencies/$categories/$countries/$borders/$base`;
  `start()`/`complete()` write `vehicle_logistics_profiles.logistics_status`
  via `updateOrCreate` (verified: no `status` column on `vehicles`).
- Views: `logistics/expenses/index.blade.php` + `logistics/advances/index.blade.php`
  created (modeled on live profitability blade); trip `show` + `reports/profitability` live.
- Menu: "Trips & Finance" group (Trips / Expenses / Driver Advances / Profitability)
  after Master Data, gated on `Trips list`, `TripExpenses list`, `Advances list`,
  `LogisticsReports list`.
- Verified: `php -l` clean (controller + routes), `view:clear`, tinker:
  19/19 Phase-3 names resolve, TripsController instantiable (abstract-columns fatal gone).

## 10. Phase-3 hardening patch — APPLIED 2026-10-11 (verified)
Source: `patches/atlas-logistics-phase3-hardening.zip` (built against snapshot,
never executed by its author; diffed line-by-line against live before applying).
- New: `SecurityHeaders` middleware (web group) + `InternalStaff` middleware
  (alias `internal.staff`, wraps logistics routes in `routes/admin.php` — C/D users get 403).
- New: `logistics:grant-role` artisan command (registered in Console Kernel) for
  non-Super-Admin roles until the Roles screen lists logistics modules.
- `TripsController` (patch version kept in full): planned-only `edit()`/`update()`
  guards, collision-proof `nextNumber()`, `setAssets()` marks/releases truck profile
  + trailer, blockers only on planned trips.
- `.gitignore` blocks `.env.*`/`*.bak`/`*.sql`; `.env.bak` removed from disk + index.
  ⚠️ History still contains `.env.bak` (commits d5e880a, a0cb034, 7c8821d, 2aa8a55)
  on public repo — needs filter-repo + rotation per README (NOT done here).
- Verified: `php -l` ×8 clean, `optimize:clear`, `route:list` still 55 logistics routes,
  `logistics:grant-role --help` renders.
