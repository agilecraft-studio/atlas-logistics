# Atlas Logistics - Security Context

- APP_DEBUG=false in prod; fresh APP_KEY on .env build.
- .env + *.key git-ignored; SMTP/hostinger password in .env only (scrubbed from docs 2026-10-08).
- Bcrypt passwords; temp passwords issued out-of-band for 5 team logins.
- DB-driven RBAC (spatie); backend gates required, not button-hiding.
- Nginx: web root public/ only; deny dotfiles; HTTPS redirect; LE cert.
- Uploads: validate MIME/size, safe names, never execute PHP, authorize access.
- Audit login/role/trip/finance/document/config changes.
