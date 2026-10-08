# Atlas Logistics - Database Context

## Server
PostgreSQL 18; DB atlas_logistics owner atlas_logistics; creds in /home/finch/.atlas-logistics-dbpass (600) and .env.

## Schema
65 tables from fleet7_db.sql via docs/fleet-pgsql.sql. SERIAL/BIGSERIAL PKs; FKs added deferred via ALTER. Sequences resynced after seed.

## Key tables
users/users_meta, vehicles/vehicles_meta, bookings/bookings_meta(+quotation), fuel, income/income_cat, expense/expense_cat, vendors, work_orders(+logs), parts(+category,+used), drivers via users(user_type D)+driver_vehicle+driver_logs, service_items/reminder, vehicle_review/types/group, roles/permissions/model pivots, oauth_*, notifications, push_subscriptions, chat_settings, api_settings, fare_settings, frontend, settings, email_content, cities, testimonials, team, company_services, reasons, reviews, messages, twilio_settings, payment_settings, booking_payments/income, driver_payments, notes, vehicle/driver_breakdown+alerts, booking_alerts.

## Notes
Seed data is Hyvikk demo (IN cities, INR, master@admin.com) except users+settings rebranded 2026-10-08. Full Malawi re-seed during customization.
