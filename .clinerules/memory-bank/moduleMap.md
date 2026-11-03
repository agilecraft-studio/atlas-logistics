# Atlas Logistics - Module Map

- Fleet/Vehicles: VehicleModel, types, groups, reviews, meta; logistics/compliance profiles.
- Drivers: users(user_type D)+meta, driver_vehicle, driver_logs, driver registration/compliance, advances.
- Trips: trips, trip_checkpoints, trip_expenses, trip_incidents (Logistics module). Passenger bookings/quotations are gone.
- Fuel/Expense/Income: fuel, expense(+cat), income(+cat).
- Maintenance: work_orders(+logs), service_items/reminder, parts(+cat,+used), vendors, mechanics.
- Inspections: vehicle_review.
- Finance: income/expense, trip_expenses, driver_advances; no online gateway checkout.
- Comms: notifications, push_subscriptions; WhatsApp API planned.
- Admin: settings, email_content, api_settings, team.
- Platform: roles/permissions, oauth, audit-ish logs, installer/updater.
- Removed 2026-11-01: bookings/quotations, taxi mobile APIs, public website, fare/payment/chat/twilio/frontend/testimonial/review/address/city/breakdown/alert modules, their code, permissions and tables.
