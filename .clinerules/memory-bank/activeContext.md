# Atlas Logistics - Active Context

## Status 2026-10-08
NOT LIVE. No Nginx vhost or SSL yet for app.atlaslogistics.co.mw (DNS points to 72.62.89.32). Laravel 13 upgrade pending.

## Done
- PHP extensions complete (gmp installed).
- PG role+DB atlas_logistics; docs/fleet-pgsql.sql regenerated clean (65 tables); loaded to test and prod DBs; sequences fixed.
- Users: blessings/emery (S), william (O), john/peter (D); demo ids 4,5,8 deleted; settings rebranded Atlas/Malawi/MK.
- Git baseline ef10ae0; remote set to SSH atlas-logistics.git (push awaits GitHub key auth).
- Email password scrubbed from docs (git-ignored, .env only).

## Next
1 push-usage scan 2 L13 composer upgrade + replacements 3 public/ assembly 4 Nginx + LetsEncrypt 5 production .env 6 smoke test 7 customization.

## Decision
WhatsApp API for notifications, NOT Slack (slack-notification-channel dropped).
