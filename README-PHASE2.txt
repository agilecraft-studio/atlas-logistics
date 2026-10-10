ATLAS LOGISTICS - PHASE 2 PATCH (fleet & driver compliance + master data)
=========================================================================
Apply on top of the project AFTER atlas-logistics-batch2.zip.

1. Back up first (git commit or copy the folder).
2. Extract this zip over the project root (same folder as artisan). Allow overwrite.
3. Run:
     php artisan migrate
   This creates 15 new tables (countries, currencies, exchange_rates, border_posts, trip_expense_categories,
   document_types, trailers, vehicle_logistics_profiles, driver_compliance_profiles, compliance_documents,
   vehicle_components, trips, trip_checkpoints, trip_expenses, driver_advances, trip_incidents),
   seeds SADC countries, currencies, border posts, expense categories and document types,
   and adds the new permissions to the "Super Admin" and "Admin" roles.
   (Trip tables are created now so Phase 3 can start immediately; no trip screens exist yet.)
4. Make sure uploaded documents can be opened:   php artisan storage:link   (skip if already done)
5. Clear caches:   php artisan view:clear && php artisan route:clear && php artisan config:clear
6. Log in as Super Admin. A new "Logistics" section appears at the top of the sidebar.

WHAT YOU GET (Logistics menu)
- Compliance Dashboard: everything expired / due in 7-90 days across trucks, trailers and drivers. Reads the
  insurance / registration / licence dates already on your Vehicles, plus new documents and driver dates.
- Fleet Register > Trucks: plate, VIN, status, depot, service due, document and tyre counts; "Logistics details" form.
- Fleet Register > Trailers, Drivers (licence, passport, medical, approved countries), Tyres & Parts (serial numbers,
  position, fitted date/odometer), Documents (insurance, permits, certificates with expiry + file upload).
- Master Data: countries, currencies, exchange rates, border posts, expense categories, document types.

IMPORTANT FIRST STEPS FOR YOU
a) Master Data > Exchange Rates: add a rate for each foreign currency you will use (base currency is MWK;
   change with LOGISTICS_BASE_CURRENCY in .env). Trip money screens (Phase 3) need these.
b) Fleet Register > Drivers: fill in each driver's licence and passport expiry. Dispatch checks (Phase 3) use them.
c) Add Documents for each truck/trailer (insurance, COMESA yellow card, roadworthy, cross-border permit...).

NOT DONE YET / KNOWN LIMITS
- Other roles (e.g. a Fleet Manager role) must be given these permissions in the database; the Roles screen still lists
  only the old modules (its list is hard-coded). Super Admin and Admin work immediately.
- Nothing here was executed: no PHP was available. Run the checklist in TEST-CHECKLIST.txt and send me any error page.
- Trips, expenses, driver advances (allowances), incidents and profitability are Phase 3 (tables already exist).
