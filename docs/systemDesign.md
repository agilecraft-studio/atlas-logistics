# SADC Cross-Border Fleet Management System

## System Design Document

**Version:** 2.0  
**Prepared for:** Malawi-Based Logistics Company  
**Operating Region:** SADC Cross-Border Transport  
**Primary Platform:** Laravel 13  
**PHP:** 8.5  
**Database:** PostgreSQL  
**Hosting:** Hostinger VPS  
**Admin Theme:** Attex Laravel Admin & Dashboard Template  
**Base Script:** CodeCanyon Fleet Manager  
**Telematics:** External application handles live GPS, IoT, fuel sensors, and advanced truck monitoring. This system focuses on fleet operations, drivers, trips, expenses, parts, documents, and profitability.

---

## 1. Executive Summary

This system is a **cross-border fleet operations and financial control platform** for a Malawi-based trucking company operating across the SADC region.

It is not intended to replace the existing telematics system. Instead, it becomes the **operational and accounting source of truth** for:

- Trucks and trailers
- Drivers and driver compliance
- Trips, jobs, loads, and delivery confirmation
- Fuel, tolls, parking, border costs, and other trip expenses
- Spare parts and workshop inventory
- Multi-currency cross-border costing
- Document expiry tracking
- Trip profitability
- Integration with the external telematics application through APIs/webhooks

The system is designed around the realities of SADC trucking:

- Landlocked Malawi origin/return operations
- Multiple border posts
- Long transit corridors
- Multi-currency expenses
- Low-network areas
- Fuel risk and reconciliation needs
- Document compliance for drivers and vehicles
- Cost leakage control

---

## 2. Business Objectives

The system must help the company achieve the following:

### 2.1 Operational Control

- Know which truck is on which trip.
- Know which driver is assigned.
- Know the load, customer, origin, destination, and expected route.
- Track trip status from dispatch to delivery.
- Track border crossings and delays.
- Store Proof of Delivery documents.

### 2.2 Financial Control

- Capture all trip-related costs:
  - Fuel
  - Tolls
  - Parking
  - Border charges
  - Permits
  - Loading/unloading charges
  - Driver allowances
  - Repairs and parts
  - Fines and penalties
  - Miscellaneous cross-border costs
- Support multiple currencies:
  - MWK
  - USD
  - ZAR
  - ZMW
  - TZS
  - MZN
  - BWP
  - NAD
  - Others as needed
- Convert all costs into base currency for reporting.
- Calculate profit per trip.
- Detect expense anomalies and possible fuel theft.

### 2.3 Fleet and Maintenance Control

- Maintain truck and trailer records.
- Track service intervals.
- Track document expiries.
- Track spare parts issued to vehicles.
- Link parts costs to trips and vehicles.
- Maintain repair and workshop history.

### 2.4 Driver Compliance

- Track driver licenses.
- Track passports.
- Track visas/permits.
- Track yellow fever or other medical certificates where applicable.
- Track countries a driver is authorized to enter.
- Prevent dispatch if critical documents are expired.

### 2.5 Reporting

The system must provide reports such as:

- Trip profitability
- Cost per kilometer
- Revenue per kilometer
- Fuel variance
- Expense breakdown by category
- Expense breakdown by country
- Border delay analysis
- Driver performance summary
- Vehicle cost history
- Parts consumption
- Document expiry alerts
- Cash advance reconciliation

---

## 3. System Scope

### 3.1 In Scope

This system will manage:

1. **Fleet Assets**
   - Trucks
   - Trailers
   - Vehicle documents
   - Service reminders
   - Odometer readings
   - Assignment history

2. **Drivers**
   - Driver profiles
   - License and passport details
   - Compliance documents
   - Trip assignment
   - Cash advances
   - Expense submissions

3. **Trips/Jobs**
   - Customer/job details
   - Load details
   - Route planning
   - Border posts
   - Dispatch status
   - Trip checkpoints
   - Delivery confirmation
   - POD uploads
   - Trip closure

4. **Expenses**
   - Fuel
   - Tolls
   - Parking
   - Border fees
   - Driver allowances
   - Repairs
   - Miscellaneous
   - Multi-currency handling
   - Receipt uploads
   - Approval workflow

5. **Parts Inventory**
   - Stock items
   - Warehouses/stores
   - Stock issue
   - Stock return
   - Purchase request
   - Issue-to-vehicle tracking
   - Trip cost allocation

6. **Financial Reconciliation**
   - Driver advances
   - Expense claims
   - Fuel reconciliation
   - Trip profit calculation
   - Cost per km calculation

7. **External Telematics Integration**
   - Receive summarized telematics data from existing IoT system
   - Sync odometer readings
   - Sync fuel consumed or fuel level events
   - Sync harsh driving events if available
   - Use synced data for trip validation and fuel variance reporting

8. **Reports and Dashboards**
   - Management dashboards
   - Operational reports
   - Financial reports
   - Compliance reports

### 3.2 Out of Scope for This Phase

The following are not directly handled inside this system at this stage:

- Live GPS tracking
- Real-time engine diagnostics
- Fuel sensor hardware integration
- Dashcam video storage
- Direct device communication
- Advanced route optimization AI
- Customs authority system integration
- Full double-entry accounting system

The existing external telematics application remains responsible for live truck monitoring.

---

## 4. Operating Context

### 4.1 Malawi-Based Cross-Border Trucking

The company operates from Malawi into the wider SADC region. Typical routes may involve corridors such as:

- Malawi to Zambia
- Malawi to DRC
- Malawi to Tanzania
- Malawi to Mozambique
- Malawi to Zimbabwe
- Malawi to South Africa
- Malawi to Botswana
- Return loads within SADC

### 4.2 Key Operational Realities

The system must account for:

- Border delays
- Multiple currencies
- Weak network coverage in remote areas
- Cash-based tolls and parking in some locations
- Manual receipt collection
- Risk of fuel loss
- Document expiry risk
- Cross-border permits
- Long trip durations
- Driver advances and reconciliation
- Breakdowns far from home depot
- Parts issued across borders

---

## 5. High-Level Architecture

### 5.1 Architecture Overview

```text
┌────────────────────────────┐
│    External Telematics     │
│  GPS / IoT / Fuel Sensors  │
└─────────────┬──────────────┘
              │ Webhooks / API
              ▼
┌──────────────────────────────────────────────┐
│              Laravel 13 FMS                  │
│                                              │
│  Fleet Module                                │
│  Driver Module                               │
│  Trip Module                                 │
│  Expense Module                              │
│  Parts Inventory Module                      │
│  Finance Module                              │
│  Reporting Module                            │
│  Document Management Module                  │
│  Telematics Sync Module                      │
└──────┬─────────────┬─────────────┬───────────┘
       │             │             │
       ▼             ▼             ▼
┌────────────┐ ┌──────────┐ ┌────────────┐
│ PostgreSQL │ │  Redis   │ │ File/Object│
│  Database  │ │ Cache +  │ │ Storage for│
│            │ │ Queues   │ │ Receipts   │
└────────────┘ └──────────┘ └────────────┘

       ▲
       │
┌──────────────────────┐
│  Attex Admin UI      │
│  Laravel Blade/Vite  │
│  Bootstrap 5.3       │
└──────────────────────┘
```

### 5.2 Main System Components

| Component | Purpose |
|---|---|
| Laravel 13 Application | Core business logic, APIs, workflows, reports |
| PostgreSQL Database | Operational and financial data storage |
| Redis | Caching, sessions, queues, rate limiting |
| Nginx | Production web server |
| PHP 8.5-FPM | Application runtime |
| Attex Theme | Admin dashboard UI |
| Supervisor | Background queue workers |
| File/Object Storage | Receipts, PODs, vehicle documents, driver documents |
| External Telematics App | Live tracking, IoT, device monitoring |
| Driver Mobile/PWA | Optional driver expense/checkpoint entry |

---

## 6. Technology Stack

### 6.1 Core Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 13 |
| PHP | PHP 8.5 |
| Database | PostgreSQL 16+ |
| Cache/Queue | Redis |
| Web Server | Nginx |
| Hosting | Hostinger VPS |
| Frontend Theme | Attex Laravel Admin Template |
| CSS Framework | Bootstrap 5.3 |
| Build Tool | Vite |
| JavaScript | Alpine.js / Vanilla JS / optional Livewire |
| API Authentication | Laravel Sanctum |
| Queue Workers | Supervisor |
| SSL | Let's Encrypt or commercial SSL |

### 6.2 Recommended Development Standards

- PHP 8.5 typed properties
- PHP Enums
- Readonly classes/properties where appropriate
- Laravel Form Requests
- Laravel Policies
- Laravel Action classes
- Laravel Resources for API responses
- Laravel Queues for heavy operations
- PostgreSQL `jsonb` for flexible route/checkpoint data
- PostgreSQL `numeric` for money fields
- Strict validation everywhere
- Feature tests for critical workflows

---

## 7. Core System Modules

---

## 7.1 Organization and Settings Module

This module controls master data used across the system.

### 7.1.1 Required Master Data

- Countries
- Currencies
- Exchange rates
- Border posts
- Expense categories
- Income categories
- Fuel types
- Vehicle types
- Trailer types
- Document types
- Roles and permissions
- Branches/depots
- Warehouses/stores
- Customers
- Suppliers
- Mechanics/workshops

### 7.1.2 Country Examples

- Malawi
- Zambia
- Democratic Republic of Congo
- Tanzania
- Mozambique
- Zimbabwe
- South Africa
- Botswana
- Namibia
- Eswatini
- Lesotho
- Angola

### 7.1.3 Currency Examples

- MWK
- USD
- ZAR
- ZMW
- TZS
- MZN
- BWP
- NAD
- LSL
- AOA

### 7.1.4 Border Post Examples

- Mwanza
- Songwe
- Tunduma
- Nakonde
- Kasumbalesa
- Chirundu
- Beitbridge
- Mloka
- Vilanculos
- Machipanda
- Ramatlabama

### 7.1.5 Exchange Rate Handling

The system should support:

- Base currency, likely MWK
- Manual rate entry
- Optional API rate source
- Effective date for each rate
- Locked rate per trip
- Historic rate for expense entries
- Audit trail for rate changes

Suggested table:

```text
exchange_rates
- id
- currency_code
- base_currency_code
- rate
- effective_date
- source
- notes
- created_by
- created_at
- updated_at
```

---

## 7.2 Fleet Asset Module

This module manages trucks and trailers.

### 7.2.1 Vehicle Record Fields

A vehicle should include:

- Vehicle ID
- Registration number
- Chassis number
- Engine number
- Make
- Model
- Year
- Color
- Vehicle type
- Axle configuration
- Fuel type
- Fuel tank capacity
- Current odometer
- Last service odometer
- Next service odometer
- Assigned depot
- Ownership type
- Insurance details
- License details
- Roadworthy details
- Cross-border permit details
- Status

### 7.2.2 Vehicle Statuses

Suggested statuses:

- Available
- On Trip
- In Workshop
- Awaiting Parts
- Suspended
- Sold
- Scrapped

### 7.2.3 Trailer Support

Since trucking operations often use trailers, include trailers separately.

```text
trailers
- id
- registration_number
- chassis_number
- trailer_type
- make
- model
- year
- payload_capacity_kg
- status
- assigned_depot_id
- notes
```

### 7.2.4 Vehicle Documents

Examples:

- Registration book
- Insurance certificate
- Roadworthy certificate
- Cross-border permit
- COMESA insurance
- Fitness certificate
- Operating license
- Dangerous goods permit if applicable

Each document should track:

- Document type
- Document number
- Issue date
- Expiry date
- File attachment
- Alert threshold
- Status

### 7.2.5 Maintenance Logic

The system should support:

- Service reminders by odometer
- Service reminders by date
- Repair history
- Workshop job cards
- Parts used during repairs
- Cost allocation to vehicle
- Cost allocation to trip if breakdown occurred during trip

---

## 7.3 Driver Module

This module manages driver records and compliance.

### 7.3.1 Driver Fields

- Driver ID
- Employee number
- Full name
- Phone number
- Alternative phone
- National ID
- Passport number
- Passport expiry
- License number
- License class
- License expiry
- Yellow fever expiry
- Visa/permit notes
- Approved SADC countries
- Home depot
- Status
- Emergency contact
- Bank/mobile money details

### 7.3.2 Driver Statuses

- Active
- On Trip
- Off Duty
- Suspended
- Terminated
- Awaiting Documents

### 7.3.3 Driver Documents

Store documents with expiry tracking:

- National ID
- Driver license
- Passport
- Visa
- Yellow fever card
- Defensive driving certificate
- Dangerous goods certificate
- Cross-border permit
- Medical certificate

### 7.3.4 Dispatch Validation Rules

Before assigning a driver to a cross-border trip, the system should validate:

- Driver is active
- License is not expired
- Passport is not expired
- Required visa/permit is not expired
- Driver is approved for destination countries
- No critical compliance warnings exist

If validation fails, dispatch should be blocked or require override by an authorized manager.

---

## 7.4 Customer and Job Module

This module handles customers and transport jobs.

### 7.4.1 Customer Fields

- Customer ID
- Customer name
- Contact person
- Phone
- Email
- Physical address
- Country
- Tax ID
- Billing currency
- Payment terms
- Notes

### 7.4.2 Job/Order Fields

A job may exist before a trip is dispatched.

```text
jobs
- id
- job_number
- customer_id
- status
- cargo_type
- cargo_description
- gross_weight_kg
- net_weight_kg
- volume_cbm
- pallet_count
- pickup_country
- pickup_city
- pickup_address
- delivery_country
- delivery_city
- delivery_address
- consignee_name
- consignee_phone
- required_pickup_date
- required_delivery_date
- quoted_amount
- quoted_currency
- notes
```

---

## 7.5 Trip Module

The trip module is the center of the system.

### 7.5.1 Trip Definition

A trip represents a specific movement of a vehicle, driver, and load.

A trip may be linked to one job or multiple jobs.

### 7.5.2 Trip Fields

```text
trips
- id
- trip_number
- job_id
- customer_id
- vehicle_id
- trailer_id
- driver_id
- co_driver_id
- status
- trip_type
- origin_country
- origin_city
- destination_country
- destination_city
- route_countries
- border_posts
- planned_start_date
- actual_start_date
- planned_end_date
- actual_end_date
- start_odometer
- end_odometer
- distance_km
- currency
- exchange_rate_locked
- revenue_amount
- revenue_currency
- revenue_amount_base
- advance_amount
- advance_currency
- notes
- created_by
- dispatched_by
- closed_by
```

### 7.5.3 Trip Status Lifecycle

Suggested trip statuses:

```text
draft
awaiting_assignment
ready_for_dispatch
dispatched
in_transit
at_border
delayed
delivered
pod_pending
awaiting_reconciliation
closed
cancelled
```

### 7.5.4 Trip Checkpoints

Because live GPS is handled elsewhere, this system should support manual and semi-automated checkpoint logging.

```text
trip_checkpoints
- id
- trip_id
- checkpoint_type
- name
- country
- latitude
- longitude
- occurred_at
- logged_by
- source
- notes
```

Checkpoint types:

- Departure
- Border Entry
- Border Exit
- Fuel Stop
- Breakdown
- Parking
- Delivery
- Return

### 7.5.5 Border Delay Tracking

For each border post crossed, capture:

- Border name
- Arrival time
- Exit time
- Delay duration
- Reason for delay
- Document issue flag
- Cost incurred

This will help produce reports such as:

- Average delay at Kasumbalesa
- Average delay at Chirundu
- Delay cost per hour
- Border performance by country

### 7.5.6 Proof of Delivery

Trip closure should require:

- Delivery date/time
- Receiver name
- Receiver phone
- Delivery condition
- POD file upload
- Delivery notes
- Exception flag if cargo was damaged/short

Use Spatie Media Library or similar for file management.

---

## 7.6 Expense Module

This is one of the most important modules for cross-border operations.

### 7.6.1 Expense Categories

Suggested categories:

- Fuel
- Toll fees
- Parking
- Border charges
- Customs facilitation
- Loading charges
- Unloading charges
- Driver allowance
- Co-driver allowance
- Accommodation
- Repairs
- Parts
- Fines
- Escort fees
- Weighbridge fees
- Communication/roaming
- Miscellaneous

### 7.6.2 Expense Fields

```text
expenses
- id
- trip_id
- vehicle_id
- driver_id
- expense_category_id
- description
- country
- city
- occurred_at
- paid_by
- original_currency
- original_amount
- exchange_rate
- base_currency
- base_amount
- receipt_required
- receipt_status
- approval_status
- approved_by
- approved_at
- notes
- created_by
```

### 7.6.3 Multi-Currency Expense Rules

Every expense must store:

1. Original amount
2. Original currency
3. Exchange rate used
4. Converted base amount

Example:

```text
Original Amount: 1,200 ZMW
Original Currency: ZMW
Exchange Rate: 1 ZMW = 75 MWK
Base Amount: 90,000 MWK
```

The system should never rely only on converted totals. Original values must remain stored for audit.

### 7.6.4 Expense Approval Workflow

Suggested approval statuses:

- Pending
- Approved
- Rejected
- Needs Clarification
- Reimbursed
- Deducted from Advance

Finance users should be able to approve or reject expenses with comments.

### 7.6.5 Receipt Handling

Each expense should support:

- Photo upload
- Multiple images
- OCR optional future feature
- Receipt number
- Vendor name
- Missing receipt flag
- Manager override note

---

## 7.7 Fuel Management Module

Fuel is one of the largest costs and one of the highest-risk areas in SADC trucking.

Even though live fuel sensors are in the external telematics system, this FMS must manage fuel financially and operationally.

### 7.7.1 Fuel Log Fields

```text
fuel_logs
- id
- trip_id
- vehicle_id
- driver_id
- fuel_type
- purchase_date
- country
- city
- station_name
- supplier
- liters
- unit_price
- total_amount
- original_currency
- exchange_rate
- base_amount
- payment_method
- fuel_card_reference
- receipt_number
- receipt_file
- odometer_at_purchase
- source
- notes
```

### 7.7.2 Fuel Reconciliation

The system should compare:

- Fuel purchased
- Fuel issued from depot if applicable
- Fuel receipts
- Telematics-reported fuel consumed
- Distance traveled
- Expected consumption per vehicle
- Actual liters per 100 km

### 7.7.3 Fuel Variance Formula

```text
Fuel Variance = Fuel Purchased/Issued - Telematics Fuel Consumed - Expected Allowance
```

Or more practically:

```text
Actual L/100km = Total Fuel Consumed / Distance KM * 100
Expected L/100km = Vehicle Standard Consumption
Variance % = (Actual - Expected) / Expected * 100
```

If variance exceeds threshold, raise an alert.

### 7.7.4 Fuel Theft Controls

The system should support:

- Receipt photo requirement
- Odometer at fuel purchase
- Country-specific fuel price sanity checks
- Duplicate receipt detection
- Fuel card reconciliation
- Telematics fuel drop alerts imported from external app
- Exception reports

---

## 7.8 Parts and Inventory Module

This module manages spare parts stocked by the company.

### 7.8.1 Inventory Items

```text
inventory_items
- id
- sku
- name
- description
- category_id
- unit_id
- vehicle_type_id
- reorder_level
- preferred_supplier
- average_cost
- last_cost
- status
```

### 7.8.2 Warehouses/Stores

```text
warehouses
- id
- name
- code
- branch_id
- country
- city
- is_active
```

Examples:

- Lilongwe Main Store
- Blantyre Store
- Lusaka Agent Store
- Dar es Salaam Agent Store
- Johannesburg Supplier Store

### 7.8.3 Stock Levels

```text
stock_levels
- id
- inventory_item_id
- warehouse_id
- quantity_on_hand
- quantity_reserved
- updated_at
```

### 7.8.4 Stock Movements

Every stock movement must be recorded.

```text
stock_movements
- id
- inventory_item_id
- warehouse_id
- movement_type
- quantity
- unit_cost
- total_cost
- reference_type
- reference_id
- vehicle_id
- trip_id
- work_order_id
- performed_by
- notes
- created_at
```

Movement types:

- Purchase In
- Issue to Vehicle
- Return to Store
- Transfer In
- Transfer Out
- Adjustment In
- Adjustment Out
- Write-off

### 7.8.5 Work Orders

```text
work_orders
- id
- work_order_number
- vehicle_id
- trailer_id
- trip_id
- workshop_id
- issue_reported
- diagnosis
- status
- opened_at
- closed_at
- labor_cost
- total_parts_cost
- total_cost
- approved_by
```

Work order statuses:

- Open
- Awaiting Parts
- In Progress
- Completed
- Cancelled

### 7.8.6 Parts-to-Trip Costing

If a part is issued during a trip, the cost should be linked to that trip.

This ensures accurate trip profitability.

---

## 7.9 Finance and Trip Profitability Module

This module calculates the true financial outcome of each trip.

### 7.9.1 Trip Revenue

Revenue may include:

- Freight charges
- Fuel surcharge
- Detention charges
- Loading/unloading charges
- Border facilitation revenue
- Other billable items

### 7.9.2 Trip Costs

Trip costs include:

- Fuel
- Tolls
- Parking
- Border fees
- Driver allowances
- Accommodation
- Repairs
- Parts
- Fines
- Miscellaneous
- Depreciation allocation optional
- Insurance allocation optional
- Overhead allocation optional

### 7.9.3 Trip Profit Formula

Basic formula:

```text
Trip Profit = Trip Revenue - Total Trip Costs
```

More detailed:

```text
Trip Profit =
  Base Revenue
- Fuel Costs
- Toll Costs
- Parking Costs
- Border Costs
- Driver Allowances
- Repair Costs
- Parts Costs
- Other Direct Costs
```

### 7.9.4 Cost Per Kilometer

```text
Cost Per KM = Total Trip Costs / Distance KM
```

### 7.9.5 Revenue Per Kilometer

```text
Revenue Per KM = Total Trip Revenue / Distance KM
```

### 7.9.6 Trip Closure Rules

A trip should only be closed when:

- POD is uploaded
- End odometer is captured or synced
- All expenses are approved or marked pending
- Fuel reconciliation is completed
- Driver advance reconciliation is completed
- Parts issued during trip are captured
- Final profit calculation is generated

---

## 7.10 Driver Advance and Reconciliation Module

Cross-border trips often require cash advances.

### 7.10.1 Advance Fields

```text
driver_advances
- id
- trip_id
- driver_id
- amount
- currency
- exchange_rate
- base_amount
- issued_by
- issued_at
- payment_method
- reference
- status
```

Advance statuses:

- Requested
- Approved
- Paid
- Partially Reconciled
- Reconciled
- Closed

### 7.10.2 Reconciliation Logic

```text
Advance Balance = Advance Issued - Approved Expenses - Cash Returned
```

The system should show:

- Total advance issued
- Total receipts submitted
- Total approved expenses
- Cash returned
- Balance owed to company
- Balance owed to driver

---

## 7.11 Telematics Integration Module

This module connects the FMS to the existing external telematics application.

### 7.11.1 Purpose

This system does not need raw GPS pings. It needs summarized operational data.

Useful data to receive:

- Vehicle identifier
- Trip start/end
- Start odometer
- End odometer
- Distance traveled
- Fuel consumed
- Fuel level events
- Harsh braking count
- Speeding events
- Idle time
- Engine hours
- Location timestamp summary
- Border arrival/exit estimates if available

### 7.11.2 Recommended Integration Method

Use a secure API endpoint.

```text
POST /api/v1/telematics/trip-summary
```

Authentication:

- Laravel Sanctum token
- Or signed webhook
- IP allowlist optional
- Rate limiting

### 7.11.3 Example Payload

```json
{
  "external_event_id": "evt_987654321",
  "external_vehicle_id": "truck-22",
  "vehicle_registration": "MWA1234",
  "external_trip_id": "TRIP-2026-00198",
  "started_at": "2026-10-08T06:15:00Z",
  "ended_at": "2026-10-10T14:40:00Z",
  "start_odometer": 452100,
  "end_odometer": 453620,
  "distance_km": 1520,
  "fuel_consumed_liters": 512.4,
  "engine_hours": 38.2,
  "idle_hours": 6.1,
  "harsh_braking_events": 3,
  "speeding_events": 5,
  "source": "telematics-app"
}
```

### 7.11.4 Idempotency

To avoid duplicate imports, store:

```text
telematics_sync_logs
- id
- external_source
- external_event_id
- payload
- processed_at
- status
- error_message
```

Add unique constraint:

```text
unique(external_source, external_event_id)
```

### 7.11.5 Reconciliation Behavior

When telemetry is received:

1. Match vehicle by registration or external ID.
2. Match active or recent trip.
3. Update trip distance and end odometer if not manually overridden.
4. Store fuel consumed.
5. Recalculate fuel variance.
6. Create alert if variance exceeds threshold.

---

## 7.12 Document Management Module

This module stores all operational and compliance documents.

### 7.12.1 Document Types

Vehicle documents:

- Registration
- Insurance
- Roadworthy
- Cross-border permit
- COMESA insurance
- Fitness certificate

Driver documents:

- License
- Passport
- Visa
- Yellow fever
- Medical certificate
- Defensive driving certificate

Trip documents:

- Job sheet
- Trip sheet
- Gate pass
- Loading note
- Delivery note
- POD
- Border documents
- Customs paperwork
- Weighbridge ticket
- Receipts

### 7.12.2 Document Requirements

Each document should support:

- File upload
- Expiry date
- Reminder threshold
- Status
- Verified by
- Verification date
- Notes

### 7.12.3 Alert Logic

Generate alerts when:

- Document expires in 30 days
- Document expires in 14 days
- Document expired
- Critical compliance document missing

---

## 7.13 Reporting and Analytics Module

### 7.13.1 Dashboards

Use Attex dashboard widgets and ApexCharts.

Management dashboard should show:

- Active trips
- Trucks on trip
- Trucks available
- Trucks in workshop
- Revenue this month
- Expenses this month
- Profit this month
- Fuel variance alerts
- Document expiry alerts
- Border delay summary

### 7.13.2 Operational Reports

- Trip status report
- Dispatch history
- Driver assignment report
- Border delay report
- POD pending report
- Vehicle utilization report
- Trailer utilization report
- Breakdown report

### 7.13.3 Financial Reports

- Trip profitability report
- Expense by category
- Expense by country
- Expense by driver
- Expense by vehicle
- Fuel cost report
- Toll cost report
- Parking cost report
- Advance reconciliation report
- Cost per km report
- Revenue per km report

### 7.13.4 Maintenance Reports

- Service due report
- Parts consumption report
- Workshop cost report
- Vehicle repair history
- Stock valuation report
- Low stock report

### 7.13.5 Compliance Reports

- Expired driver documents
- Expired vehicle documents
- Drivers without valid passport
- Vehicles without valid insurance
- Trips dispatched with override

---

## 8. PostgreSQL Database Design Principles

PostgreSQL is a strong choice for this system. Use its features intentionally.

### 8.1 Use `jsonb` for Flexible Data

Use `jsonb` for:

- Route countries
- Border checkpoint lists
- Expense metadata
- Telemetry payloads
- Custom fields

Example:

```php
$table->jsonb('route_countries')->nullable();
$table->jsonb('border_posts')->nullable();
$table->jsonb('metadata')->nullable();
```

Add GIN indexes where searching inside JSON is needed.

```php
$table->index('border_posts')->using('gin');
```

### 8.2 Use `numeric` for Money

Do not use float.

```php
$table->numeric('original_amount', 18, 2);
$table->numeric('exchange_rate', 18, 6);
$table->numeric('base_amount', 18, 2);
```

### 8.3 Use Timestamps Properly

Use timezone-aware timestamps.

```php
$table->timestampTz('occurred_at')->nullable();
$table->timestampTz('approved_at')->nullable();
```

### 8.4 Use Database Constraints

Add constraints where appropriate.

Examples:

- Expense original amount cannot be negative unless adjustment type
- Exchange rate must be greater than zero
- Stock movement quantity must be valid for movement type
- Unique trip number
- Unique external telematics event ID

### 8.5 Useful PostgreSQL Extensions

Enable if needed:

```sql
CREATE EXTENSION IF NOT EXISTS pgcrypto;
CREATE EXTENSION IF NOT EXISTS pg_trgm;
```

- `pgcrypto`: UUID generation if used
- `pg_trgm`: fuzzy search for vehicle plates, driver names, receipts, etc.

### 8.6 Indexing Strategy

Index foreign keys and frequently filtered columns.

Suggested indexes:

```text
trips:
- status
- vehicle_id
- driver_id
- customer_id
- started_at
- ended_at

expenses:
- trip_id
- expense_category_id
- approval_status
- occurred_at
- country

fuel_logs:
- trip_id
- vehicle_id
- purchase_date

stock_movements:
- inventory_item_id
- warehouse_id
- movement_type
- created_at

documents:
- documentable_type
- documentable_id
- expires_at
```

---

## 9. Recommended Database Tables

Below is a practical table list for the final system.

### 9.1 Master Tables

```text
countries
currencies
exchange_rates
branches
warehouses
customers
suppliers
vehicle_types
vehicle_statuses
trailer_types
driver_statuses
document_types
expense_categories
income_categories
border_posts
```

### 9.2 Fleet Tables

```text
vehicles
trailers
vehicle_documents
vehicle_assignments
service_reminders
odometer_readings
```

### 9.3 Driver Tables

```text
drivers
driver_documents
driver_approvals
driver_advances
```

### 9.4 Trip Tables

```text
jobs
trips
trip_drivers
trip_checkpoints
trip_border_crossings
trip_documents
trip_status_histories
```

### 9.5 Expense Tables

```text
expenses
expense_receipts
expense_approvals
expense_adjustments
```

### 9.6 Fuel Tables

```text
fuel_logs
fuel_reconciliations
fuel_variance_alerts
```

### 9.7 Inventory Tables

```text
inventory_items
inventory_categories
inventory_units
stock_levels
stock_movements
stock_transfers
stock_adjustments
purchase_requests
```

### 9.8 Workshop Tables

```text
work_orders
work_order_parts
work_order_labour
```

### 9.9 Integration Tables

```text
telematics_sync_logs
telematics_trip_summaries
telematics_events
api_clients
webhook_logs
```

### 9.10 Security Tables

```text
users
roles
permissions
role_user
permission_role
activity_logs
audit_logs
login_attempts
```

---

## 10. Key Business Rules

### 10.1 Dispatch Rules

A trip may only be dispatched if:

- Vehicle is available
- Vehicle documents are valid
- Trailer is available if required
- Driver is active
- Driver documents are valid
- Driver is approved for destination countries
- Start odometer is captured or synced
- Trip customer/job details are complete
- Route countries are selected

### 10.2 Expense Rules

- Expenses must belong to a trip, vehicle, or workshop cost center.
- Cross-border expenses must capture country.
- Multi-currency expenses must capture original currency and rate.
- Receipt is required unless flagged and approved.
- Expenses above threshold require manager approval.

### 10.3 Fuel Rules

- Fuel purchase must include liters, price, currency, receipt, and odometer if possible.
- Fuel variance above threshold creates alert.
- Fuel logs without receipts require manager override.

### 10.4 Parts Rules

- Stock cannot go negative unless explicitly allowed for emergency issue.
- Every part issue must reference a vehicle or work order.
- Parts issued during a trip should be linked to the trip.
- Stock adjustments require reason and audit trail.

### 10.5 Trip Closure Rules

A trip cannot be closed if:

- POD is missing
- End odometer is missing
- Pending expenses are unresolved
- Advance reconciliation is incomplete
- Fuel reconciliation is incomplete
- Critical exceptions remain open

---

## 11. User Roles and Permissions

### 11.1 Suggested Roles

| Role | Access |
|---|---|
| Super Admin | Full access |
| Fleet Manager | Fleet, drivers, trips, reports |
| Dispatcher | Trip creation, driver assignment, checkpoint updates |
| Finance | Expenses, advances, profitability, reports |
| Workshop Manager | Work orders, parts requests, vehicle repairs |
| Storekeeper | Inventory issue/receive/transfer |
| Driver | Own trips, expense entry, POD upload |
| Auditor | Read-only access to financial and audit logs |
| Management | Dashboards and reports |

### 11.2 Permission Examples

- `trips.view`
- `trips.create`
- `trips.dispatch`
- `trips.close`
- `expenses.view`
- `expenses.create`
- `expenses.approve`
- `fuel.view`
- `fuel.create`
- `fuel.reconcile`
- `inventory.view`
- `inventory.issue`
- `inventory.adjust`
- `documents.verify`
- `reports.financial`
- `settings.manage`

Use Spatie Laravel Permission for this.

---

## 12. UI/UX Design Using Attex Theme

Attex provides a strong Bootstrap 5 admin foundation. Use it as follows.

### 12.1 Main Navigation

Suggested sidebar:

- Dashboard
- Trips
- Fleet
- Drivers
- Customers
- Expenses
- Fuel
- Inventory
- Workshop
- Documents
- Reports
- Telematics Sync
- Settings
- Audit Logs

### 12.2 Dashboard Widgets

Use Attex widgets and ApexCharts:

- Active trips count
- Available trucks count
- Trucks in workshop
- Monthly revenue
- Monthly expenses
- Net profit
- Pending expense approvals
- Fuel variance alerts
- Document expiry alerts
- Border delay summary

### 12.3 Trip Pages

Required pages:

- Trip list
- Create trip
- Trip detail
- Trip timeline
- Trip expenses
- Trip fuel logs
- Trip checkpoints
- Trip documents
- Trip POD
- Trip reconciliation
- Trip closure

### 12.4 Driver Pages

- Driver list
- Driver profile
- Driver documents
- Driver trips
- Driver advances
- Driver expense claims

### 12.5 Vehicle Pages

- Vehicle list
- Vehicle profile
- Vehicle documents
- Vehicle trips
- Vehicle expenses
- Vehicle fuel history
- Vehicle workshop history
- Vehicle odometer history

### 12.6 Expense Pages

- Expense list
- Pending approvals
- Add expense
- Expense receipt viewer
- Expense category report
- Multi-currency summary

### 12.7 Inventory Pages

- Item list
- Stock levels
- Stock movement history
- Issue parts
- Return parts
- Stock transfer
- Low stock report

### 12.8 Mobile Considerations

Drivers and field staff need simple mobile screens.

Driver mobile views should include:

- Current trip
- Add checkpoint
- Add expense
- Upload receipt
- Upload POD
- Request advance
- Report breakdown
- Request parts

Because connectivity can be poor, consider a PWA or mobile app with offline queueing.

---

## 13. Customization Plan for CodeCanyon Fleet Manager

The purchased Fleet Manager script provides a useful base but must be heavily customized for cross-border logistics.

### 13.1 What to Keep

Useful base features likely include:

- Vehicle management
- Driver management
- Income/expense base structure
- Booking/job base structure
- Basic inventory
- Basic reports
- Admin authentication
- Settings screens

### 13.2 What Must Be Changed

#### 13.2.1 Database Changes

- Add countries table
- Add currencies table
- Add exchange rates table
- Add border posts table
- Add multi-currency fields to expenses/incomes
- Add trip lifecycle fields
- Add driver compliance fields
- Add vehicle/trailer document tracking
- Add stock movement audit table
- Add telematics sync tables
- Add receipt/document media tables
- Add audit logs

#### 13.2.2 Business Logic Changes

- Replace simple booking status with full trip state machine
- Add dispatch validation rules
- Add expense approval workflow
- Add driver advance reconciliation
- Add fuel variance calculation
- Add trip profitability calculation
- Add document expiry alerts
- Add stock movement costing
- Add external telematics sync service

#### 13.2.3 UI Changes

- Replace default dashboard with logistics dashboard
- Add Attex-based trip detail page
- Add expense approval screen
- Add driver compliance screen
- Add document expiry screen
- Add fuel reconciliation screen
- Add stock movement screen
- Add border delay report screen

#### 13.2.4 Code Refactoring

Do not keep adding logic to controllers.

Move logic into:

```text
app/Domain/Fleet
app/Domain/Drivers
app/Domain/Trips
app/Domain/Expenses
app/Domain/Fuel
app/Domain/Inventory
app/Domain/Telematics
app/Domain/Reporting
app/Support
```

Use:

- Actions
- Services
- DTOs
- Enums
- Policies
- Form Requests
- Eloquent Scopes
- API Resources

### 13.3 Recommended Module Structure

```text
app/
├── Domain/
│   ├── Fleet/
│   ├── Drivers/
│   ├── Trips/
│   ├── Expenses/
│   ├── Fuel/
│   ├── Inventory/
│   ├── Workshop/
│   ├── Telematics/
│   ├── Finance/
│   └── Reporting/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   ├── Resources/
│   └── Middleware/
├── Services/
├── Enums/
├── Policies/
└── Support/
```

---

## 14. Recommended Laravel Extensions

Below are the recommended packages and extensions.

### 14.1 Core Packages

| Package | Purpose | Priority |
|---|---|---|
| `spatie/laravel-permission` | Roles and permissions | Critical |
| `spatie/laravel-medialibrary` | Receipts, PODs, documents | Critical |
| `spatie/laravel-activitylog` | Activity audit trail | Critical |
| `laravel/sanctum` | API authentication for telematics/driver app | Critical |
| `yajra/laravel-datatables-oracle` | Server-side datatables for large lists | High |
| `barryvdh/laravel-dompdf` | PDF trip sheets, gate passes, receipts | High |
| `spatie/browsershot` | Advanced HTML-to-PDF rendering | Optional |
| `brick/money` | Accurate money calculations | High |
| `brick/math` | Precise decimal arithmetic | High |
| `akaunting/laravel-money` | Currency formatting helper | Optional |
| `propaganistas/laravel-phone` | Phone number validation for SADC countries | Medium |
| `laravel/horizon` | Redis queue monitoring | High |
| `laravel/pint` | Code style | High |
| `larastan/larastan` | Static analysis | High |
| `pestphp/pest` | Testing | High |
| `spatie/laravel-backup` | Database/file backups | Critical |
| `sentry/sentry-laravel` | Error tracking | High |

### 14.2 Optional Packages

| Package | Purpose | Priority |
|---|---|---|
| `livewire/livewire` | Reactive forms without full SPA | Optional |
| `livewire/volt` | Modern Livewire single-file components | Optional |
| `laravel/folio` | Page-based routing | Optional |
| `spatie/laravel-model-states` | Trip/work order state machine | Medium |
| `spatie/laravel-query-builder` | API filtering | Medium |
| `spatie/laravel-fractal` | API response transformation | Optional |
| `knuckleswtf/scribe` | API documentation | Medium |
| `laravel/fortify` | Stronger authentication backend | Medium |
| `spatie/laravel-feature-flags` | Feature toggles | Medium |
| `meilisearch/meilisearch-php` | Fast search if needed | Optional |
| `laravel/scout` | Search abstraction | Optional |

### 14.3 Business Feature Extensions

Beyond packages, add these business extensions:

- Multi-currency ledger module
- Driver advance reconciliation module
- Border delay tracking module
- Document compliance module
- Fuel reconciliation module
- Trip profitability module
- Stock movement audit module
- Telematics sync module
- Offline driver PWA
- SMS/WhatsApp notifications
- Customer portal optional
- Supplier portal optional
- Mobile money payment tracking optional

---

## 15. API Design

### 15.1 API Purpose

The API will support:

- External telematics integration
- Future driver mobile app
- Future customer portal
- Future supplier/workshop portal

### 15.2 API Versioning

Use versioned endpoints:

```text
/api/v1
```

### 15.3 Core Endpoints

#### Trips

```text
GET    /api/v1/trips
GET    /api/v1/trips/{id}
POST   /api/v1/trips
PATCH  /api/v1/trips/{id}
POST   /api/v1/trips/{id}/dispatch
POST   /api/v1/trips/{id}/checkpoints
POST   /api/v1/trips/{id}/pod
POST   /api/v1/trips/{id}/close
```

#### Expenses

```text
GET    /api/v1/expenses
POST   /api/v1/expenses
POST   /api/v1/expenses/{id}/receipts
PATCH  /api/v1/expenses/{id}
POST   /api/v1/expenses/{id}/approve
POST   /api/v1/expenses/{id}/reject
```

#### Fuel

```text
GET    /api/v1/fuel-logs
POST   /api/v1/fuel-logs
GET    /api/v1/trips/{id}/fuel-reconciliation
```

#### Drivers

```text
GET    /api/v1/drivers
GET    /api/v1/drivers/{id}
GET    /api/v1/drivers/{id}/documents
```

#### Vehicles

```text
GET    /api/v1/vehicles
GET    /api/v1/vehicles/{id}
GET    /api/v1/vehicles/{id}/documents
```

#### Telematics

```text
POST   /api/v1/telematics/trip-summary
POST   /api/v1/telematics/fuel-event
POST   /api/v1/telematics/odometer-update
GET    /api/v1/telematics/sync-logs
```

### 15.4 API Authentication

Use Laravel Sanctum tokens.

Example header:

```text
Authorization: Bearer YOUR_TOKEN
```

For external telematics webhooks, consider:

- Bearer token
- HMAC signature
- IP allowlist
- Rate limiting
- Idempotency key

---

## 16. Security Design

### 16.1 Application Security

- HTTPS everywhere
- Strong password policy
- Optional 2FA for admin/finance
- Role-based access control
- CSRF protection
- Rate limiting
- Input validation
- Audit logging
- Encrypted secrets
- Environment separation

### 16.2 Database Security

- PostgreSQL should not be exposed publicly
- Use strong database credentials
- Restrict access to localhost or private network
- Use SSL for database connections if remote DB is used
- Regular backups

### 16.3 File Security

- Uploaded receipts should not be publicly accessible
- Use signed URLs or authenticated file access
- Validate file types
- Limit file sizes
- Store files outside web root if possible

### 16.4 Audit Requirements

Log changes to:

- Trips
- Expenses
- Fuel logs
- Stock adjustments
- Exchange rates
- User roles
- Document approvals
- Trip closure
- Advance reconciliation

---

## 17. Performance Design

### 17.1 Caching

Use Redis for:

- Exchange rates
- Country lists
- Currency lists
- Dashboard aggregates
- Permission checks
- Session storage
- Rate limiting

### 17.2 Queues

Use queues for:

- Sending emails
- Sending SMS
- Generating PDFs
- Processing receipt uploads
- Importing telematics data
- Creating notifications
- Recalculating trip profitability
- Generating reports

### 17.3 Database Performance

- Add indexes on foreign keys
- Use pagination everywhere
- Avoid N+1 queries
- Use Eloquent eager loading
- Use database views for reporting if needed
- Use materialized views for heavy dashboards if needed

### 17.4 Reporting Strategy

For large data volumes:

- Use read-only reporting tables
- Use scheduled aggregation jobs
- Use PostgreSQL views/materialized views
- Avoid running heavy reports on live transaction tables during peak hours

---

## 18. Hostinger VPS Production Design

### 18.1 Recommended VPS Size

Minimum for production:

- 4 vCPU
- 8 GB RAM
- NVMe SSD
- Ubuntu 24.04 LTS

Better for growth:

- 8 vCPU
- 16 GB RAM
- NVMe SSD

### 18.2 Server Software

Recommended:

- Ubuntu 24.04
- Nginx
- PHP 8.5-FPM
- PostgreSQL 16 or newer
- Redis
- Supervisor
- Certbot SSL
- UFW firewall
- Fail2ban

### 18.3 Suggested Directory Layout

```text
/home/fleet/app          # Laravel application
/home/fleet/storage      # Uploaded files if local storage used
/home/fleet/backups      # Database/file backups
/etc/nginx/sites-available/fleet.conf
/etc/supervisor/conf.d/fleet-worker.conf
```

Web root:

```text
/home/fleet/app/public
```

### 18.4 Environment Variables

Example `.env` production settings:

```env
APP_NAME="SADC Fleet"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fleet.yourcompany.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fleet_production
DB_USERNAME=fleet_user

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

FILESYSTEM_DISK=local
MEDIA_DISK=local

TELEMATICS_WEBHOOK_SECRET=CHANGE_THIS
BASE_CURRENCY=MWK
```

### 18.5 PHP 8.5-FPM Tuning

Recommended `php.ini` settings:

```ini
memory_limit = 512M
upload_max_filesize = 25M
post_max_size = 30M
max_execution_time = 120
max_input_vars = 5000

opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=25000
opcache.validate_timestamps=0

opcache.jit=1255
opcache.jit_buffer_size=128M
```

After deployment, restart PHP-FPM:

```bash
sudo systemctl restart php8.5-fpm
```

### 18.6 PostgreSQL Tuning

Example starting values for an 8GB RAM VPS:

```conf
shared_buffers = 2GB
effective_cache_size = 6GB
work_mem = 16MB
maintenance_work_mem = 512MB
max_connections = 100
```

For 16GB RAM:

```conf
shared_buffers = 4GB
effective_cache_size = 12GB
work_mem = 32MB
maintenance_work_mem = 1GB
```

### 18.7 Supervisor Queue Worker

Example:

```ini
[program:fleet-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/fleet/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=4
user=www-data
redirect_stderr=true
stdout_logfile=/home/fleet/storage/logs/worker.log
```

Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start fleet-worker:*
```

### 18.8 Firewall

Use UFW:

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

Do not expose:

- PostgreSQL port 5432
- Redis port 6379

Unless using a secure private network and strict firewall rules.

### 18.9 SSL

Use Certbot:

```bash
sudo certbot --nginx -d fleet.yourcompany.com
```

### 18.10 Backups

Use both:

1. Hostinger VPS snapshots
2. Application-level PostgreSQL backups

Example backup command:

```bash
pg_dump -U fleet_user -Fc fleet_production > /home/fleet/backups/fleet_$(date +%F_%H%M).dump
```

Recommended backup policy:

- Daily database dump
- Weekly full file backup
- Monthly off-site archive
- Test restore quarterly

---

## 19. Deployment Strategy

### 19.1 Recommended Deployment Approach

Use a zero-downtime deployment tool such as:

- Deployer
- Envoyer
- GitHub Actions with SSH deploy script
- GitLab CI with SSH deploy

### 19.2 Deployment Steps

Typical production deployment:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
npm ci
npm run build
sudo systemctl reload nginx
sudo systemctl restart php8.5-fpm
sudo supervisorctl restart fleet-worker:*
```

### 19.3 Laravel Optimization Commands

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 19.4 Rollback Plan

Always maintain:

- Previous release directory
- Database backup before migration
- Ability to restore previous code quickly
- Maintenance mode switch

---

## 20. Testing Strategy

### 20.1 Feature Tests

Write tests for critical workflows:

- Create trip
- Dispatch trip
- Add expense
- Approve expense
- Convert multi-currency expense
- Log fuel purchase
- Receive telematics sync
- Calculate fuel variance
- Issue part to vehicle
- Close trip
- Generate trip profit report

### 20.2 Unit Tests

Test calculations:

- Currency conversion
- Trip profitability
- Cost per km
- Fuel variance
- Stock movement totals
- Advance reconciliation

### 20.3 Browser Tests

Optional but useful:

- Laravel Dusk for critical admin flows
- Mobile responsive testing for driver screens

### 20.4 Static Analysis

Use:

```bash
./vendor/bin/pint
./vendor/bin/phpstan analyse
```

---

## 21. Notification Strategy

### 21.1 Internal Alerts

Send alerts for:

- Document expiry
- Service due
- Fuel variance
- Expense approval required
- Trip delayed
- POD missing
- Negative stock
- Telematics sync failure

### 21.2 Channels

- Dashboard notifications
- Email
- SMS
- WhatsApp optional

### 21.3 SMS Providers for SADC

Consider:

- Africa's Talking
- Twilio
- Local Malawian SMS gateway
- Mobile money notification integration if needed

---

## 22. Offline and Low-Connectivity Strategy

Because SADC routes include poor network areas, the system should support offline-capable field entry.

### 22.1 Recommended Approach

Use a Progressive Web App or native mobile app for drivers.

### 22.2 Offline Features

Drivers should be able to:

- View current trip
- Add checkpoints
- Add expenses
- Take receipt photos
- Record fuel purchases
- Upload POD when connection returns
- Report breakdown

### 22.3 Sync Design

- Store offline entries locally
- Queue entries with unique local IDs
- Sync when network is available
- Handle conflicts by timestamp and user
- Mark synced/failed records
- Allow manual retry

### 22.4 Receipt Upload Strategy

- Compress images before upload
- Store offline until connection available
- Use resumable uploads if possible
- Attach trip and expense IDs after sync if needed

---

## 23. Future Expansion Ideas

These can be added after the core system is stable.

### 23.1 Customer Portal

Customers can view:

- Job status
- Trip status
- ETA summaries
- POD documents
- Invoices

### 23.2 Supplier Portal

Suppliers can:

- Receive purchase requests
- Confirm part availability
- Submit quotations
- Update delivery status

### 23.3 Fuel Card Integration

If using fuel card providers, integrate:

- Fuel card transactions
- Station data
- Volume purchased
- Card reference numbers

### 23.4 Mobile Money Integration

Track or process:

- Driver advances
- Expense reimbursements
- Customer payments

### 23.5 Customs/Border Authority Integration

Future integration with:

- Customs systems
- Port authority systems
- Regional transit tracking systems

### 23.6 Business Intelligence

Export to:

- Metabase
- Power BI
- Looker Studio

---

## 24. Implementation Roadmap

### Phase 1: Foundation

Duration: 2–3 weeks

Tasks:

- Set up Hostinger VPS
- Install Nginx, PHP 8.5, PostgreSQL, Redis
- Deploy Laravel 13 base
- Install Attex theme
- Configure authentication
- Configure roles and permissions
- Configure master data tables
- Set up countries, currencies, exchange rates

Deliverables:

- Working admin portal
- Basic settings module
- Production environment ready

### Phase 2: Fleet, Drivers, and Documents

Duration: 2–4 weeks

Tasks:

- Customize vehicle module
- Add trailer support
- Add driver compliance fields
- Add document management
- Add expiry alerts
- Add vehicle/driver reports

Deliverables:

- Fleet registry
- Driver registry
- Compliance dashboard

### Phase 3: Trip and Dispatch Module

Duration: 3–5 weeks

Tasks:

- Customize booking/job into trip module
- Add SADC route fields
- Add border checkpoint tracking
- Add trip status lifecycle
- Add dispatch validation
- Add POD upload
- Add trip timeline

Deliverables:

- Full trip workflow
- Dispatch controls
- Border delay capture

### Phase 4: Expenses, Fuel, and Advances

Duration: 3–5 weeks

Tasks:

- Add multi-currency expense module
- Add expense approval workflow
- Add receipt upload
- Add fuel logs
- Add driver advances
- Add reconciliation logic

Deliverables:

- Trip cost capture
- Expense approval process
- Driver advance reconciliation

### Phase 5: Inventory and Workshop

Duration: 2–4 weeks

Tasks:

- Customize parts inventory
- Add warehouses
- Add stock movements
- Add work orders
- Add parts issue to vehicle/trip
- Add stock reports

Deliverables:

- Inventory control
- Workshop cost tracking
- Parts-to-trip costing

### Phase 6: Telematics Bridge and Reporting

Duration: 2–4 weeks

Tasks:

- Build telematics API endpoint
- Build sync logs
- Receive odometer/fuel summaries
- Build fuel variance report
- Build trip profitability report
- Build executive dashboard

Deliverables:

- Integrated telematics summaries
- Profitability dashboard
- Fuel variance alerts

### Phase 7: Testing, Training, Go-Live

Duration: 2–3 weeks

Tasks:

- User acceptance testing
- Pilot with selected trucks
- Train dispatchers, finance, drivers
- Correct workflow issues
- Final deployment

Deliverables:

- Production go-live
- Training materials
- Support process

---

## 25. Acceptance Criteria

The system is ready for production when:

### Fleet

- All trucks and trailers are registered.
- Vehicle documents are stored and expiry alerts work.
- Vehicle status changes correctly based on trips/workshop.

### Drivers

- All drivers are registered.
- Compliance documents are stored.
- Dispatch validation prevents non-compliant assignment.

### Trips

- A trip can be created, dispatched, tracked, delivered, and closed.
- Border checkpoints can be logged.
- POD can be uploaded.
- Trip status history is maintained.

### Expenses

- Expenses can be captured in multiple currencies.
- Exchange rates are applied correctly.
- Receipts can be uploaded.
- Approval workflow works.

### Fuel

- Fuel purchases can be logged.
- Telematics fuel consumption can be synced.
- Fuel variance report works.

### Inventory

- Stock can be received, issued, returned, and adjusted.
- Stock movements are auditable.
- Parts issued to a trip affect trip cost.

### Telematics

- External telematics app can send trip summaries securely.
- Duplicate sync events are prevented.
- Odometer and fuel data update the correct trip.

### Reports

- Trip profit report is accurate.
- Expense report is accurate.
- Fuel variance report is accurate.
- Document expiry report is accurate.

### Infrastructure

- System is accessible over HTTPS.
- Database is not publicly exposed.
- Backups are scheduled.
- Queue workers are running.
- Error logging is active.

---

## 26. Risks and Mitigation

| Risk | Mitigation |
|---|---|
| CodeCanyon script not fully compatible with Laravel 13/PHP 8.5 | Refactor carefully, upgrade incrementally, or port required modules into a clean Laravel 13 app |
| Poor driver adoption | Build simple mobile screens, minimize required fields, allow offline entry |
| Missing receipts | Enforce receipt upload, allow exception approval with audit trail |
| Fuel theft | Combine receipts, odometer, telematics sync, variance alerts |
| Border delays distort costing | Capture border delay times separately and report them |
| Multi-currency errors | Store original amount, rate, and converted amount; use precise numeric types |
| Server failure | Hostinger snapshots plus off-site database backups |
| Data entry errors | Validation rules, approval workflow, audit logs |
| Low connectivity | Offline PWA/mobile sync design |
| Scope creep | Deliver in phases, stabilize core operations first |

---

## 27. Recommended Immediate Next Steps

### Step 1: Prepare Server

- Provision Hostinger VPS
- Install Ubuntu 24.04
- Install Nginx, PHP 8.5-FPM, PostgreSQL, Redis, Supervisor
- Configure firewall and SSL

### Step 2: Prepare Base Application

- Install Laravel 13
- Install Attex theme
- Install CodeCanyon Fleet Manager
- Configure PostgreSQL connection
- Configure Redis cache/queue
- Set up environment variables

### Step 3: Extend Database

- Add countries, currencies, exchange rates
- Add SADC border posts
- Extend trips with cross-border fields
- Extend expenses with multi-currency fields
- Extend drivers with compliance fields
- Extend vehicles with document tracking

### Step 4: Build Core Workflows

- Trip creation
- Dispatch validation
- Expense capture
- Expense approval
- Fuel logging
- Driver advance reconciliation

### Step 5: Integrate Telematics

- Create secure API endpoint
- Define payload format
- Build sync log table
- Build odometer/fuel import logic
- Build variance report

### Step 6: Deploy Pilot

- Use 3–5 trucks first
- Run parallel manual tracking for one month
- Validate profit calculations
- Train users
- Adjust workflows

---

## 28. Final Design Principles

This system should always follow these principles:

1. **The truck is the asset.**
2. **The driver is the operator.**
3. **The trip is the cost center.**
4. **The load/job is the revenue source.**
5. **Every cost must be linked to a vehicle, driver, trip, or workshop event.**
6. **Every multi-currency transaction must preserve the original amount.**
7. **Every document must have an expiry and an owner.**
8. **Every stock movement must be auditable.**
9. **Every telematics import must be idempotent.**
10. **Every trip must close with a clear profit or loss position.**

---

## 29. Short Version for Developers

If your development team needs the shortest possible instruction set:

- Use **Laravel 13** as the core application.
- Use **PHP 8.5** with OPcache and JIT enabled.
- Use **PostgreSQL** with `jsonb` for routes/checkpoints and `numeric` for money.
- Host on **Hostinger VPS** using **Nginx + PHP-FPM + Redis + Supervisor**.
- Keep the existing telematics app separate.
- Build a **secure API endpoint** to receive telematics trip summaries.
- Customize the CodeCanyon Fleet Manager script to support:
  - Multi-currency expenses
  - SADC border checkpoints
  - Driver compliance
  - Vehicle/trailer documents
  - Trip lifecycle
  - Fuel reconciliation
  - Parts inventory
  - Trip profitability
- Use **Attex** for the admin dashboard.
- Use **Spatie Permission**, **Spatie Media Library**, **Spatie Activity Log**, **Laravel Sanctum**, **Laravel Horizon**, **Brick Money**, and **Yajra Datatables**.
- Build with an **offline-capable driver PWA/mobile layer** as soon as possible.

---

This is now a complete system design for your **SADC cross-border fleet management platform** using your exact stack: **Laravel 13, PHP 8.5, PostgreSQL, Hostinger VPS, Attex theme, CodeCanyon Fleet Manager base, and external telematics app**.