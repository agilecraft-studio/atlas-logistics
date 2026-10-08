# Atlas Logistics - Workflow Context

- Trip lifecycle target: Planned/Scheduled/Assigned/Loading/Loaded/Dispatched/In Transit/At Border/Delivered/Completed/Cancelled.
- Legacy status ints in bookings; map to controlled values during customization.
- Prevent vehicle/driver double-assignment conflicts.
- Money: NUMERIC; never delete financial history (void/reverse).
- Reminders: maintenance, document expiry, daily summaries via scheduler.
- Scheduler cron: artisan schedule:run each minute from /var/www/atlas-logistic.
