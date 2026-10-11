# Atlas Logistics - Progress

## 2026-10-08 go-live track
- Env: PHP 8.5 extensions verified (gmp installed); composer+packagist reachable; certbot present.
- DB: atlas_logistics role+DB; fleet-pgsql.sql regenerated (77414 chars, 65 tables, deferred FKs); test import clean; prod loaded; sequences reset.
- Identity: 5 team logins set; demo accounts removed; settings rebranded.
- Git: baseline ef10ae0; SSH remote configured; push pending key auth.
- Memory bank: 12 files required; projectBrief pre-existing; productContext/systemPatterns/techContext/activeContext written this session.


## 2026-10-09
- Fixed IDE diagnostic errors in PaymentController.php & Admin/HomeController.php:
  * Added missing 'use Illuminate\Support\Facades\Mail;' and 'use App\Mail\CustomerInvoice;'
  * Added proper facades 'use Illuminate\Support\Facades\Auth;' and 'use Illuminate\Support\Facades\DB;'
  * Commented out deprecated/eluceo iCal references (package not installed)
  * Commented out debug test() method
- Resolved npm/composer dependency issues:
  * Created missing package.json for Laravel project
  * Ran npm install (40 packages installed)
  * Ran composer install (PHP dependencies including razorpay/razorpay 2.9.3)

## Status
- Dependencies resolved; IDE diagnostics fixed
- Still pending: Nginx vhost, SSL/Let's Encrypt, public/ directory assembly, production .env
- Pending: databaseContext/securityContext/moduleMap/workflowContext/testingContext/decisionLog/progress(this file).
## 2026-10-09 Atlas Logistics landing & login branding
- Customised public-facing landing (frontend.home) and login flow (auth.login, customer_dashboard.login/forgot_password, sign_up) to brand Atlas Logistics as a freight & grain transport company serving Malawi and Southern Africa.
- Added configurable frontend keys (landing_description, landing_book_now, login_tagline, sign_up_sub_title) and updated app_name/manifest defaults in the seeder.
- Verified: PHP lint clean; view diffs reviewed.

## Status
- Landing & login branding implemented and verified.
- Still pending: databaseContext/securityContext/moduleMap/workflowContext/testingContext/decisionLog (and remaining items listed above).


## 2026-10-10 Phase 2 applied (logistics compliance + master data)
- Applied `patches/atlas-logistics-phase2` on development @ ea1cd63: 36 new files + 2 edited (`routes/admin.php` hook inside the auth/officeadmin group; sidebar `@include('layouts.attex.menu-logistics')`), plus README-PHASE2.txt / TEST-CHECKLIST-PHASE2.txt at root.
- DB backup taken first: /var/backups/atlas_logistics-pre-phase2-20261010-163600.sql.gz (gzip verified).
- `php artisan migrate --force` ran all 4 new migrations (2026_10_11_000001..4): 16 tables created, SADC seeds (12 countries, 12 currencies, 11 border posts, 10 expense categories, 19 document types), 35 logistics permissions granted to Super Admin + Admin roles.
- `storage:link` created; view/route/config caches cleared.
- Fixed one patch bug found during testing: `logistics/crud/form.blade.php` called `str_contains()` on array `rules` (owner regex rule) -> 500 on documents/components create forms. One-line fix applied to live file and patch copy.
- Verified with scripted HTTP suite (34/34 PASS): all 18 GET pages 200 as Super Admin incl. dashboard with Logistics menu; trailer create/edit/duplicate-reject/soft-delete; vehicle + driver profile saves; tyre fit with owner split; PDF upload to public disk; compliance due-in-10-days detection; driver (no roles) denied on logistics routes + menu hidden; zero test rows/files left after cleanup.
- Known limits: Roles screen module list still hard-coded (other roles need DB grants - Phase 3); exchange_rates empty until rates entered.
- Security fix applied (requested by patch author): removed unauthenticated GET /admin/migrate and /admin/clear_cache routes from routes/admin.php (confirmed 404 live; installer /migrate route is separately gated by canInstall middleware and left untouched per approved decision). Migrations now run via CLI only.
- Other pre-existing issues found, NOT fixed (untouched files): home.blade reads $_SERVER['HTTP_USER_AGENT'] directly (500 only if UA header absent); drivers/profile.blade crashes on $row->customer->name when a booking's customer_id has no matching users row (bookings 1 and 2 both point to missing users 4 and 5).
- Phase 3 pending: trip lifecycle, dispatch checks, expenses/advances/incidents, profitability (trip tables already exist).

## 2026-10-10 (2) Fixed all open errors from today's laravel-2026-10-10.log
- Settings save500s (3 occurrences): SettingsController::store() wrote $request->traccar_username/password/server_link but the form has no such inputs (they live on the dedicated traccar_settings screen via TrackerController) -> null into NOT NULL settings.value. Removed the 3 orphaned writes (coalescing them would have blanked the real traccar credentials on every save). Also added `?? ''` to fare_mode/return_booking updates (unchecked checkboxes post null; they would have crashed next).
- vehicle_review_index: orphaned review user (force-deleted user) -> `$r->user->name ?? '—'` (page 500 -> 200 verified).
- drivers/profile null customer (bookings.customer_id -> missing users 4/5) -> `?? '—'` in both Completed/Upcoming rows (driver dashboard 500 -> 200 verified).
- home.blade +4 sibling views read $_SERVER['HTTP_USER_AGENT'] directly -> `?? ''` (6 occurrences; dashboard 500 without UA -> 200 verified).
- drivers/index `count($errors)` on null -> `count($errors ?? [])` (log context was a CLI render harness).
- Verified: reproduce-first script /tmp/logfix_verify.php 11/11 PASS after fixes; Phase 2 suite 34/34 still PASS; php -l clean; blades compile.
- Not actionable: ParseError routes/admin.php:344 entries (04:20-04:27, transient pre-session edit, file lints clean now); /tmp/render_view.php offsetSet error (external temp harness, not app code).

- Phase 3 pending: trip lifecycle, dispatch checks, expenses/advances/incidents, profitability (trip tables already exist).


## 2026-10-11 (PM) installer hard-removed + FULL fleet UI migration + log fixes
- Installer/licensing fully deleted (controllers, IsInstalled/canInstall middleware, requests, helpers, config/installer.php, views, VersionAutoUpdate, storage/installed marker). All installation/installed/migrate/migration/upgrade* URLs verified 404 on the live vhost; zero code traces (grep app/routes/bootstrap/config/resources).
- Fleet shell de-zoomed (negative margins removed, font scale normalized) + fleet.css legacy Bootstrap/AdminLTE compat layer + layouts/fleet-legacy.blade.php runtime (LOCAL jQuery+moment, PNotify shim, modal shim, CSRF/route globals).
- All 137 remaining attex/app views migrated to layouts.fleet (mechanical: layout/section renames, 397 fa->bi icons, 8 dual script sections merged, 133 breadcrumbs dropped). view:cache clean; 51-URL authenticated HTTP sweep 200+fleet-shell; final 20-URL sweep produced ZERO new log entries.
- Log-error fixes: bookings_meta sum(text) PostgreSQL crash cast to numeric at 15 sites (reports/drivers + related, was 500); customers report null-relation crash nullsafe at 6 sites; expenses/index $m ordering bug (page 500) fixed; reports/driver chart asset path repaired.
- Commits: a5a0369 (installer removal), 0860590 (shell de-zoom + legacy runtime), ba766ff (137-view migration), c940005 (report fixes). Push to origin still pending GitHub key auth.
- Env note: php-fpm runs as `atlas`; CLI runs as `finch` cannot overwrite atlas-owned 644 cache files in storage/framework/cache (logged as file_put_contents permission denied when harnesses run the file cache). Live vhost unaffected. Use `sudo -u atlas` or CACHE array for CLI harnesses.
