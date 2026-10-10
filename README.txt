ATLAS LOGISTICS - THEME BATCH 2 PATCH
=====================================
Apply on top of the project you uploaded as atlas-logistics-development (2).zip
(i.e. the version that already has the Attex shell + the earlier PostgreSQL / English-only fixes).

1. Back up first:      git commit -am "before batch 2"   (or copy the folder)
2. Extract this zip over the project root (same folder as artisan). Allow overwrite.
3. Delete the files listed in DELETE-THESE-FILES.txt (3 stray probe files).
     Linux/Mac:  xargs -d '\n' rm -f < DELETE-THESE-FILES.txt
4. Clear caches:       php artisan view:clear && php artisan route:clear && php artisan config:clear
5. Rebuild assets only if you changed JS/SCSS (this patch did not):  npm run build
6. Smoke test in a browser (see TEST-CHECKLIST.txt). Nothing here was run in PHP or a browser.

WHAT CHANGED
- 105 views converted to the Attex (Bootstrap 5) layout: vehicles, drivers, service_reminder,
  vehicle_breakdown, work_orders, parts, parts_category, service_items, vendors, mechanics, fuel,
  expense, income, users, roles, utilities, notifications, driver_alert, vehicle_types, vehicle_groups,
  customers, reports.
- NOT converted on purpose: bookings, booking_quotation (to be replaced by Trips). They keep the old layout.
- Attex shell fixes (layouts/attex/): DataTables core now loads before its Bootstrap integration;
  added global CSRF ajaxSetup + auto DataTables for #data_table/#data_table1/#data_table11 (legacy-init);
  added Font Awesome + DataTables/select2/pnotify/fullcalendar CSS (legacy-css); added PDF export fonts.
- tools/convert_views.py: reusable converter. Example:  python3 tools/convert_views.py resources/views/bookings
- CANDIDATES-FOR-REMOVAL.md: screens I did NOT delete; tell me which to remove.
