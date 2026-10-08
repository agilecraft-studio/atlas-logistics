# Atlas Logistics — Project Brief

## Purpose
Atlas Logistics Fleet & Transport Management System for a Malawi / Southern Africa
grain-transport company. Logistics operations app (fleet, trips, cargo, fuel,
maintenance, finance) — NOT a taxi/passenger-booking business.

## Production target
- Domain: https://app.atlaslogistics.co.mw
- Server: Ubuntu VPS at 72.62.89.32 (this machine; DNS verified 2026-10-08)
- Web root must be: /var/www/atlas-logistic/public (currently MISSING)
- Stack: Ubuntu + Nginx + PHP-FPM + PostgreSQL + HTTPS (Let's Encrypt)
- Sender: noreply@atlaslogistics.co.mw, SMTP creds in .env only

## Codebase (as found 2026-10-08)
- Hyvikk Fleet Manager v6.1 ported to Laravel 10 (`laravel/framework ^10.0`, PHP `^8.0`)
- Location: /var/www/atlas-logistic (note: singular, no trailing s; NOT a git repo)
- vendor/ present (146M); app/, config/, routes/, storage/ present
- MISSING: public/, resources/views+assets, database/migrations, .env.example,
  tests, git history. Stub .env (4 lines only).
- DB artefact: storage/fleet7_db.sql is a MySQL/MariaDB dump (InnoDB, backticks)
  for database `f7` — NOT PostgreSQL compatible as-is.

## Go-live status: BLOCKED (see activeContext.md + decisionLog.md)
