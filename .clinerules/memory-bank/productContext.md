# Atlas Logistics — Product Context

## Business
Atlas Logistics is a Malawi / Southern Africa grain-transport company.
This app runs logistics operations: fleet, vehicles, drivers, trips/dispatch,
cargo, routes (incl. cross-border), fuel, maintenance, inspections, expenses,
income, vendors, documents, compliance, reporting, notifications, RBAC, audit.

## Not a taxi app
No passenger/taxi flows remain. The 2026-11-01 purge deleted the booking
system, taxi mobile APIs, public marketing site, fare/payment/chat/twilio
settings, testimonials, reviews, addresses, cities and their tables. Trips are
the only transport records.

## Notifications direction (2026-10-08)
WhatsApp API for operational notifications (trip assignment, status changes,
maintenance/document reminders, alerts). Slack notifications will NOT be used
— `laravel/slack-notification-channel` to be dropped in the Laravel 13 upgrade.

## Mail
Default sender noreply@atlaslogistics.co.mw via smtp.hostinger.com:465 (SSL).
Credentials in `.env` only. Team: blessings + emery (admins), william (manager),
john + peter (drivers).
