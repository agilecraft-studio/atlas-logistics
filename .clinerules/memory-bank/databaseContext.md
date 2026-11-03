# Atlas Logistics - Database Context

## Server
PostgreSQL 18; DB atlas_logistics owner atlas_logistics; creds in /home/finch/.atlas-logistics-dbpass (600) and .env.

## Schema
59 tables after the 2026-11-01 taxi purge (was 65 + logistics additions). SERIAL/BIGSERIAL PKs; FKs added deferred via ALTER. Sequences resynced after seed.

## Key tables
users/users_meta, vehicles/vehicles_meta(+logistics/compliance profiles), trips(+checkpoints,+expenses,+incidents), trailers, driver_advances, fuel, income/income_cat, expense/expense_cat, vendors, work_orders(+logs), parts(+category,+used), drivers via users(user_type D)+driver_vehicle+driver_logs, service_items/reminder, vehicle_review/types/group, roles/permissions/model pivots, oauth_*, notifications, push_subscriptions, api_settings, settings, email_content, team, countries/currencies/exchange_rates/border_posts, document_types/compliance_documents, notes.

## Notes
Taxi-era tables dropped 2026-11-01 by migration `2026_11_01_000001_drop_legacy_taxi_tables` (pre-removal dump in database/backups/legacy_taxi_pre_removal_*.sql.gz). Seed data is Atlas Logistics demo except users+settings rebranded 2026-10-08. Full Malawi re-seed during customization.
