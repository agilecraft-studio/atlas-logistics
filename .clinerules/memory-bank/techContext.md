# Atlas Logistics - Technical Context

## Backend
Atlas Logistics Fleet Manager v6.1; Laravel 10 now, target Laravel 13 latest; PHP 8.5; PostgreSQL 18 (atlas_logistics DB, role atlas_logistics).

## PHP extensions (verified 2026-10-08)
BCMath, Ctype, Fileinfo, GMP (installed), JSON, Mbstring, OpenSSL, PDO, PDO_PGSQL, Tokenizer, XML, Zip, plus curl/gd/intl.

## Frontend
Blade views (~296), Bootstrap/AdminLTE-style layouts, jQuery-era assets under web-root assets/.

## Key packages
spatie/laravel-permission, spatie/laravel-backup, laravel/passport, maatwebsite/excel, yajra/datatables-oracle, barryvdh/laravel-dompdf, kreait/firebase, pusher.
Dropped on L13: laravelcollective/html, kodeine/laravel-meta, edujugon/push-notification, sly/notification-pusher, laravel/helpers, legacy-factories, slack-notification-channel.
