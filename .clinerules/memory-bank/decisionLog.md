# Atlas Logistics - Decision Log

## 2026-10-09 (round 3: Api/api1 SQL, date casts, route integrity, English-only)
- Q1: Applied patch `patches/atlas-logistics-fixes-three`. Verified live on pgsql (PHP + DB available).
- Q2: Api/api1 GROUP BY fixes: `Api/`+`api1/` VendorApiController make/model/color lists, api1/UsersApi get_make/get_model (distinct), Backend/VendorsApiController types now select only grouped column. get_month_years uses `as year`/`as month`. CURDATE()/YEAR() covered by round-2 migration.
- Q3: Date-format search filters now cast to timestamp (`col::timestamp`) so pgsql date_format(typed for timestamp) matches date/timestamp/text columns. Applied to Bookings, Quotations, Drivers, Vehicles, Work Orders.
- Q4: Route integrity (verified): removed dead `/track/{id?}`, `get-driver-index`, `/reports/vehicle` (no method/view/link). Fixed `Income@income_records` typo -> `IncomeController@income_records` (method exists at IncomeController.php:104). Verified: app boots (862 routes), income_records -> IncomeController, track-driver kept, removed routes absent.
- Q5: English-only: deleted 9 non-English lang folders (Albanian/Arabic/French/German/Italian/Portuguese/Romanian/Spanish/Traditional Chinese) + English-en/frontend.php. Pruned empty dirs; only English-en + vendor remain (+ stray .DS_Store left). Verified: zero remaining `frontend.*` translation refs in app/resources.
- Q6: Locale hardcoded to `English-en` in SetLocale, SetLocaleUser middleware + Login/ForgotPassword/ResetPassword controllers. AtlasLogistics::frontend() helper still used elsewhere (CheckFrontEnable, FrontendApiController) so not orphaned.
- Q7: Language pickers in settings/single_tax_settings/changepass -> hidden `value="English-en"`. frontend.php keys in customers/show + drivers/verifydriver -> fleet keys. Added `identification_number` key to English-en/fleet.php (verified present inside array, php -l clean).
- Q8: CORRECTION re-applied (round-3 patch was built on round-2 base and REVERTED my fixes): re-added `MIN(vehicle_id) as vehicle_id` + quoted `"Income2"` to all 3 delinquent income queries (ReportsController x2, ReportsApiController x1). Verified E2E on pgsql: Income2=4018.00, day=Monday, vehicle_id populated, no GROUP BY error.
- Q9: Verified: 20 changed PHP files + fleet.php pass php -l; migration already Ran (idempotent, "Nothing to migrate"); optimize:clear OK; route:list OK. NOT run in a browser.
- Q10: Remaining (not in this patch, reported): leftover RTL/dead code still unused; frontend.* lang keys confirmed now fully removed.

## 2026-10-09 (PostgreSQL DataTables / report SQL porting)
- P1: Applied patch `patches/atlas-logistics-fixes-two` (Postgres compatibility for admin DataTables + reports).
- P2: Existing migration `2026_10_08_000001_add_mysql_compatibility_functions` already provides YEAR/MONTH/DAY/MONTHNAME/DATE_FORMAT/IFNULL; those call sites left untouched.
- P3: New migration `2026_10_09_000001_add_curdate_dayname_compatibility_functions` adds `CURDATE()` and `DAYNAME()` (verified live on pgsql: curdate()=2026-10-09, dayname()=Friday). Ran successfully, batch [3].
- P4: Search filters changed `LIKE`->`ILIKE` (Postgres LIKE is case-sensitive) across Bookings, Quotations, Drivers, Vehicles, Vehicle Types, Work Orders. Yes/No + Paid/Pending filters hardened with `payment::text IN ('1','true','t')` so they work for int or boolean columns.
- P5: GROUP BY fixes: Vehicles make/model/color lists, Vendors type list, and driver/customer/delinquent reports now select only grouped/aggregated columns. `get_models` uses `distinct()`. Report driver/customer grouping uses `MIN(id)`/`MIN(vehicle_id)`.
- P6: CORRECTION to patch (verified against views): delinquent income query keeps `MIN(vehicle_id) as vehicle_id` because `reports/delinquent.blade.php` + `print_delinquent.blade.php` + `ReportsApiController` read `$row->vehicle_id`/`$row->vehicle`. Patch had dropped it; only `income_cat` was genuinely unused.
- P7: CORRECTION to patch (pre-existing pgsql bug, found while verifying): `sum(amount) as Income2` alias was unquoted, so Postgres folded it to `income2` and the views' `$row->Income2` rendered blank. Quoted as `"Income2"` in all 3 delinquent queries; verified `Income2=4018.00` etc. Views left unchanged.
- P8: Verified live on pgsql: migration ran; corrected delinquent Eloquent query returns date/day/Income2/vehicle_id with no GROUP BY error; all 9 controllers + migration pass `php -l`; app boots (route:list OK). NOT run in a browser.
- P9: Known-remaining (reported to user, not in this patch): `Api`/`api1` mobile controllers still contain YEAR()/CURDATE() and unchecked GROUP BY queries; `frontend.*` lang keys still present.

## 2026-10-08
- D1: PostgreSQL (not MySQL) per rules; converted dump via custom tokenizer script; original MySQL file kept read-only.
- D2: Upgrade Laravel 10 to 13 latest (not incremental LTS stay).
- D3: Drop dead packages: collective/html, kodeine/meta, edujugon push, sly pusher, helpers, legacy-factories, slack-channel.
- D4: WhatsApp API for notifications instead of Slack.
- D5: Team emails replace demo seed users; demos deleted.
- D6: GitHub SSH atlas-logistics.git; local-first commits until key authorized.
- D7: SMTP password scrubbed from docs into .env only.
- D8: Adopted Karpathy-inspired AI behavioural guidelines as `.clinerules/karpathy-guidelines.md`; referenced from `.clinerules.md` "AI Behavior / Coding Discipline". Atlas rules always take precedence; "no configurability" rule narrowed to forbid only speculative code flexibility, not configurable business/reference data.


## 2026-10-08 (rebrand: Hyvikk -> Atlas Logistics)
- R1: Renamed app brand from Hyvikk to Atlas Logistics (app.atlaslogistics.co.mw) - own the product, remove original vendor association.
- R2: Rebrand script (sed Hyvikk -> Atlas Logistics) corrupted PHP identifiers (spaces in class/namespace/use names). Fixed using valid single-word identifier AtlasLogistics for code; kept spaced brand in display strings/URLs/emails.
- R3: Model App\Model\Hyvikk -> App\Model\AtlasLogistics; global alias key in config/app.php updated to AtlasLogistics so Blade views resolve.
- R4: Regenerated config:clear && config:cache; cleared view:clear; regenerated stale compiled Blade views.
- R5: Emails/URLs updated to app domain; .env APP_NAME -> Atlas Logistics.
- R6: Verified: full php -l = 0 errors (651 files); config:cache OK; routes /, /login, /admin/login = 200; home title 'Home | Atlas Logistics'.
