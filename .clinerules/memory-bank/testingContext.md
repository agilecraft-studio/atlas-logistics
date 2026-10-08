# Atlas Logistics - Testing Context

- No tests in repo (Hyvikk base ships none).
- Post-upgrade: add tests for auth, RBAC, vehicle/driver assignment, trip lifecycle, finance math, fuel validation, doc expiry, notifications incl. WhatsApp stub.
- Verify: php artisan test (once app boots on L13), PG import row counts, HTTPS + redirect, mail send via noreply.
