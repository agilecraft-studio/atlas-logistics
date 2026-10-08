# Atlas Logistics - System Patterns

## Base
Hyvikk Fleet Manager v6.1 on Laravel 10, upgrading to 13.
Source split: framework dir is the Laravel app; top-level assets/uploads/index.php is web-root content.
Prod dir /var/www/atlas-logistic mirrors framework.

## Data
Eloquent models in app/Model. Meta tables users_meta/vehicles_meta/bookings_meta via kodeine package (to replace with internal trait).
Views use laravelcollective Form/Html in ~138 files (to replace with plain Blade).

## Auth
Spatie Permission roles. Passport API tokens. DB-driven RBAC only.

## Notifications
Legacy edujugon/sly push packages are abandoned: replace with WebPush channel plus planned WhatsApp API service. No Slack.

## Install
Web installer imports MySQL dump; no migrations dir. PG schema in docs/fleet-pgsql.sql (65 tables, FKs deferred).
