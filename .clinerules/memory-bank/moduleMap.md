# Atlas Logistics - Module Map

- Fleet/Vehicles: VehicleModel, types, groups, reviews, meta; 44 admin controllers.
- Drivers: users(user_type D)+meta, driver_vehicle, driver_logs, payments.
- Trips: bookings(+quotation,meta,income,payments,alerts); taxi frontend to disable.
- Fuel/Expense/Income: fuel, expense(+cat), income(+cat).
- Maintenance: work_orders(+logs), service_items/reminder, parts(+cat,+used), vendors, mechanics.
- Inspections: vehicle_review.
- Finance: income/expense, booking_payments, driver_payments, payment_settings.
- Comms: notifications, push_subscriptions, chat_settings, twilio_settings; WhatsApp API planned.
- Admin: settings, frontend, email_content, api_settings, cities, team, testimonials, company_services.
- Platform: roles/permissions, oauth, audit-ish logs, installer/updater.
