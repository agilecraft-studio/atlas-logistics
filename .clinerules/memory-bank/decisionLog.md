# Atlas Logistics - Decision Log

## 2026-10-08
- D1: PostgreSQL (not MySQL) per rules; converted dump via custom tokenizer script; original MySQL file kept read-only.
- D2: Upgrade Laravel 10 to 13 latest (not incremental LTS stay).
- D3: Drop dead packages: collective/html, kodeine/meta, edujugon push, sly pusher, helpers, legacy-factories, slack-channel.
- D4: WhatsApp API for notifications instead of Slack.
- D5: Team emails replace demo seed users; demos deleted.
- D6: GitHub SSH atlas-logistics.git; local-first commits until key authorized.
- D7: SMTP password scrubbed from docs into .env only.
