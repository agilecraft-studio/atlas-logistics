# Atlas Logistics — Product Context

## Business
Atlas Logistics is a Malawi / Southern Africa grain-transport company.
This app runs logistics operations: fleet, vehicles, drivers, trips/dispatch,
cargo, routes (incl. cross-border), fuel, maintenance, inspections, expenses,
income, vendors, documents, compliance, reporting, notifications, RBAC, audit.

## Not a taxi app
No passenger booking/taxi flows. Booking/quotation tables inherited from the
Atlas Logistics base are freight-trip records; taxi-style frontend (cities, fares,
testimonials) will be disabled or repurposed during customization.

## Notifications direction (2026-10-08)
WhatsApp API for operational notifications (trip assignment, status changes,
maintenance/document reminders, alerts). Slack notifications will NOT be used
— `laravel/slack-notification-channel` to be dropped in the Laravel 13 upgrade.

## Mail
Default sender noreply@atlaslogistics.co.mw via smtp.hostinger.com:465 (SSL).
Credentials in `.env` only. Team: blessings + emery (admins), william (manager),
john + peter (drivers).
