🏗️ REZIO CHATA – Technical Project Brief & Architecture

Project Type: SaaS Property Management System (PMS)
Framework: Laravel 10+
Architecture: Multi-Database Tenancy (Database-per-tenant via stancl/tenancy)

1. Core Architecture Principles

1.1 Hybrid Identity System (ID Strategy)

We use a Hybrid ID strategy to balance performance, compatibility, and security.

Internal/Admin Entities: Use BigInt (Auto Increment).

Reason: Compatibility with Laravel packages (Spatie, ActivityLog), faster joins, simpler internal logic.

Tables: users, properties, services, seasons, blackout_dates.

Public/Transactional Entities: Use UUID.

Reason: Security (unguessable URLs), Data Merging capabilities, Obfuscation of business volume.

Tables: bookings, customers, guests, folios, invoices, payments.

1.2 The Snapshot Pattern (Financial Integrity)

We enforce strict Immutability of History.

Prices in Booking and Invoice MUST NOT depend on the live Service or Season catalog.

Implementation: Data is copied from catalogs to booking_items and invoice_items with fixed prices at the moment of creation.

1.3 The Folio System (Billing Logic)

We implement an enterprise-grade billing hierarchy:

Booking: The time reservation container.

Folio: A "virtual wallet" or "account" within the booking (allows split billing).

Booking Items: Actual charges (nights, fees) attached to a Folio.

Invoice: A frozen snapshot of a paid Folio.

2. Data Modeling & Namespaces

We organize Eloquent models by Domain context, not just a flat list.

📂 Root

App\Models\Property (The God Object - BigInt ID)

App\Models\User (Admin/Staff - BigInt ID)

📂 App\Models\Booking

Booking (UUID): Holds check_in_date, check_out_date, relations to Customer/Property.

Folio (UUID): Holds groupings of items ("Main Bill", "Bar Bill").

BookingItem (UUID): Represents a dynamic charge line (linked to booking_items table).

📂 App\Models\CRM

Customer (UUID): The Payer / Invoice Contact. Distinguishes between Person (first/last) and Company (company_name, ico).

Guest (UUID): The Sleeper. Holds GDPR data (passport, birth date) for accommodation logs.

📂 App\Models\Finance

Invoice (UUID): Immutable tax document. Contains copied supplier/customer data.

InvoiceItem (UUID): Immutable line item.

Payment (UUID): Transaction record linking transaction_reference (Stripe/Bank) to a Folio.

📂 App\Models\Configuration (formerly Inventory)

Service (BigInt): Catalog of extra fees/items.

Season (BigInt): Date ranges for pricing logic.

BlockDate (BigInt): Maintenance/Blackout ranges.

3. Naming Conventions (Strict Style Guide)

This convention must be enforced to maintain "Clean Code".

Concept

Convention

Example

Money

Integer (Cents) + Suffix _amount

total_price_amount, unit_price_amount

Tax

Integer + Suffix _amount / _rate

tax_amount (value), tax_rate (2100 = 21%)

Dates (Stay)

check_in_date / check_out_date

Date type (Y-m-d)

Dates (Valid)

start_date / end_date

Date type (Y-m-d)

Timestamps

Suffix _at

paid_at, issued_at, reminders_sent_at

Booleans

Prefix is_, has_, can_

is_active, is_company, has_vat

External IDs

transaction_reference

String (Stripe ID, VS)

Enums

PHP Backed Enums

BookingStatus::Confirmed, PriceType::PerNight

4. Key Workflows & Logic

4.1 Availability Calculation

Availability is determined by checking for overlaps in two layers:

Block Dates: (Maintenance, Owner stay) -> Table blackout_dates.

Existing Bookings: (Confirmed/Pending) -> Table bookings.

Logic: (StartA < EndB) && (EndA > StartB)

4.2 Booking Creation Flow

Frontend sends date_from, date_to.

Backend calculates price using Property::getPriceForDates().

Create Booking (UUID).

Create default Folio ("Hlavní účet").

Create BookingItem (Type: Night) -> Snapshot of the calculated price.

Create BookingItem (Type: Service) -> e.g. Breakfast.

4.3 Invoicing Flow

Select a Folio to bill.

Create Invoice (UUID) with a unique sequence number.

SNAPSHOT: Copy Property address to supplier_address.

SNAPSHOT: Copy Customer address to customer_address.

SNAPSHOT: Copy BookingItems from Folio to InvoiceItems.

Mark Folio as invoiced.

5. Database Schema (Visual Overview)

erDiagram
    User ||--o{ Property : manages
    Property ||--o{ Booking : owns
    
    Customer ||--o{ Booking : "makes (Payer)"
    Booking ||--o{ Guest : "contains (Sleepers)"
    
    Booking ||--o{ Folio : "billing accounts"
    Folio ||--o{ BookingItem : contains
    Folio ||--o{ Payment : "paid via"
    
    Booking ||--o{ Invoice : "generated docs"
    Invoice ||--o{ InvoiceItem : contains
    
    Property ||--o{ Service : defines
    Property ||--o{ Season : defines
    Property ||--o{ BlockDate : defines


6. Implementation Checklist (Next Steps)

[ ] Ensure all migrations follow the Hybrid ID pattern.

[ ] Verify App/Models folder structure matches the Namespaces defined above.

[ ] Create API Resources to adapt check_in_date to frontend date_from (if needed).

[ ] Implement BlockDate::scopeOverlaps for calendar logic.

[ ] Set up Model Casts for Money fields (integer) and Dates (date).