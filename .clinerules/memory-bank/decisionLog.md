# Atlas Logistics - Decision Log

## 2026-10-08
- D1: PostgreSQL (not MySQL) per rules; converted dump via custom tokenizer script; original MySQL file kept read-only.
- D2: Upgrade Laravel 10 to 13 latest (not incremental LTS stay).
- D3: Drop dead packages: collective/html, kodeine/meta, edujugon push, sly pusher, helpers, legacy-factories, slack-channel.
- D4: WhatsApp API for notifications instead of Slack.
- D5: Team emails replace demo seed users; demos deleted.
- D6: GitHub SSH atlas-logistics.git; local-first commits until key authorized.
- D7: SMTP password scrubbed from docs into .env only.
- D8: Adopted Karpathy-inspired AI behavioural guidelines as `.clinerules/karpathy-guidelines.md`; referenced from `.clinerules.md` "AI Behavior / Coding Discipline". Atlas rules always take precedence; "no configurability" rule narrowed to forbid only speculative code flexibility, not configurable business/reference data.


## 2026-10-08 (rebrand: Hyvikk -> Atlas Logistics)
- R1: Renamed app brand from Hyvikk to Atlas Logistics (app.atlaslogistics.co.mw) - own the product, remove original vendor association.
- R2: Rebrand script (sed Hyvikk -> Atlas Logistics) corrupted PHP identifiers (spaces in class/namespace/use names). Fixed using valid single-word identifier AtlasLogistics for code; kept spaced brand in display strings/URLs/emails.
- R3: Model App\Model\Hyvikk -> App\Model\AtlasLogistics; global alias key in config/app.php updated to AtlasLogistics so Blade views resolve.
- R4: Regenerated config:clear && config:cache; cleared view:clear; regenerated stale compiled Blade views.
- R5: Emails/URLs updated to app domain; .env APP_NAME -> Atlas Logistics.
- R6: Verified: full php -l = 0 errors (651 files); config:cache OK; routes /, /login, /admin/login = 200; home title 'Home | Atlas Logistics'.
