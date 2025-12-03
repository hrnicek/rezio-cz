=== foundation rules ===

# Rezio Project Guidelines

The Rezio guidelines are specifically curated for this multi-tenant property booking management SaaS application. These guidelines should be followed closely to maintain consistency and scalability.

## Foundational Context
This application is a Laravel 12 multi-tenant SaaS for property rental management with booking systems. Key technologies and versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.15
- filament/filament (FILAMENT) - v4.2.4
- inertiajs/inertia-laravel (INERTIA) - v2.0.11
- laravel/framework (LARAVEL) - v12.40.2
- livewire/livewire (LIVEWIRE) - v3.7.0
- stancl/tenancy (TENANCY) - v3.x
- @inertiajs/vue3 (INERTIA_VUE) - v2.2.7
- tailwindcss (TAILWINDCSS) - v4.1.14
- vue (VUE) - v3.5.22

## Application Architecture
- **Multi-tenant SaaS** - Each tenant has isolated database, storage, and cache
- **Domain-based tenancy** - Tenants accessed via subdomains (tenant.rezio.test)
- **Property booking system** - Core business logic around property rentals
- **Seasonal pricing** - Dynamic pricing based on seasons with priority system
- **State machines** - Booking, Payment, Invoice, and Folio state management
- **Event-driven** - Domain events with listeners for business processes

## Conventions
- Follow existing multi-tenant patterns - tenant data isolation is critical
- Use UUIDs for primary keys in tenant models (security, enumeration prevention)
- Implement soft deletes for audit trails and data recovery
- Use explicit return types and parameter types in all methods
- Prefer service classes and actions over fat controllers
- Use data transfer objects (DTOs) for complex data structures

## Business Logic Patterns
- **Booking lifecycle**: Creation → Validation → Confirmation → Payment → Check-in/out → Completion
- **Pricing calculation**: Seasonal pricing + additional services with quantity support
- **Availability rules**: Business rules for minimum stay, persons, seasonal restrictions
- **Payment processing**: Multiple payment methods with state tracking
- **Invoice generation**: Automatic proforma and confirmation invoices

=== tenancy rules ===

## Multi-Tenancy (Stancl\Tenancy)

- **Database isolation** - Each tenant gets separate database (tenant{uuid})
- **Domain identification** - Tenants identified by subdomain routing
- **Central domains** - rezio.test, rezio.cz, staging.rezio.cz for central app
- **Resource syncing** - Use when tenant data needs to reference central data
- **Tenant-aware storage** - Files, cache, sessions automatically scoped per tenant

### Tenant Creation Flow
- Database created automatically via JobPipeline
- Migrations run on new tenant database
- Optional seeding for initial data
- Domain mapping established

### Tenant Context
- Always verify tenant context in tenant-specific operations
- Use tenant() helper to access current tenant
- Central operations should never assume tenant context

=== booking rules ===

## Booking System

### Core Entities
- **Booking** - Aggregate root with UUID, state machine, soft deletes
- **Property** - Rental property with seasonal pricing
- **Customer** - Client with booking history
- **Guest** - Individual guests associated with bookings
- **Folio** - Accounting ledger for booking items
- **Payment** - Payment records with state tracking
- **Invoice** - Generated invoices with state machine

### Booking States
- **Pending** - Initial state after creation
- **Confirmed** - Booking confirmed, awaiting payment
- **Paid** - Payment received
- **CheckedIn** - Guest arrived
- **CheckedOut** - Guest departed
- **Completed** - Booking finished, final invoicing
- **Cancelled** - Booking cancelled
- **NoShow** - Guest didn't arrive

### Business Rules
- **Availability validation** - Check property availability before booking
- **Minimum stay** - Enforce minimum night requirements
- **Minimum persons** - Validate guest count requirements
- **Seasonal restrictions** - Full season booking rules
- **Block dates** - Prevent booking on blocked dates

=== pricing rules ===

## Pricing System

### Seasonal Pricing
- **Priority system** - Higher priority seasons override lower ones
- **Date ranges** - Seasons defined by start/end dates
- **Default season** - Fallback pricing when no season matches
- **Daily calculation** - Price computed per night across stay duration

### Service Pricing
- **PerNight** - Service cost × nights × quantity
- **PerDay** - Service cost × days × quantity
- **Fixed** - One-time service cost × quantity
- **PerPerson** - Cost per person × guest count
- **PerStay** - Fixed cost for entire booking

### Price Breakdown
- **Accommodation** - Base property cost
- **Services** - Additional service costs
- **Total** - Sum with currency handling
- **Money casting** - Consistent money handling with currency

=== filament rules ===

## Admin Interface (Filament v4)

### Structure
- **Central admin** - Tenant and domain management
- **Tenant admin** - Property, booking, customer management per tenant
- **Resource organization** - Separate namespaces for Central vs Tenant resources

### Key Resources
- **PropertyResource** - CRUD properties with seasons and services
- **BookingResource** - Booking management with state transitions
- **CustomerResource** - CRM with import/export capabilities
- **TenantResource** - Central tenant administration

### Features
- **Dashboard widgets** - Key metrics and recent activity
- **Calendar integration** - Booking calendar views
- **Export functionality** - Data export for reporting
- **Bulk operations** - Mass updates where applicable

=== inertia-vue rules ===

## Frontend (Inertia.js + Vue 3)

### Page Structure
```
resources/js/pages/
├── Admin/     # Filament admin pages
├── Auth/      # Authentication flows
├── Central/   # Central app pages
├── Guest/     # Guest check-in interface
└── Widget/    # Booking widget pages
```

### Component Patterns
- **Single root element** - Vue component requirement
- **Form components** - Use Inertia Form helper for reactive forms
- **State management** - Vue composables for complex state
- **Route generation** - Ziggy for Laravel route URLs in JS

### Widget System
- **Property display** - Calendar, pricing, service selection
- **Real-time validation** - AJAX availability checking
- **Progressive booking** - Step-by-step booking flow
- **Payment integration** - Multiple payment method support

=== state-management rules ===

## State Machines

### BookingState
- **Transitions** - Controlled state changes with validation
- **Events** - State change events for business logic triggers
- **Persistence** - Database-backed state storage

### PaymentState
- **Pending** → **Paid** → **Refunded**
- **Failed** - Payment processing errors
- **Cancelled** - Payment cancelled

### InvoiceState
- **Draft** → **Issued** → **Paid**
- **Cancelled** - Invoice cancellation

### FolioState
- **Open** → **Invoiced** → **Closed**
- Accounting state management

=== event-driven rules ===

## Domain Events & Listeners

### Key Events
- **BookingCreated** - Trigger notifications, proforma invoice
- **PaymentCreated/Updated/Deleted** - Sync payment state
- **BookingCompleted** - Final processing and cleanup

### Listeners
- **CreateProformaInvoice** - Automatic invoice generation
- **CreatePaymentConfirmationInvoice** - Payment confirmation
- **HandlePaymentDeleted** - Cleanup on payment removal

### Event Flow
- Events dispatched from model observers
- Listeners handle cross-cutting concerns
- Queued processing for performance

=== testing rules ===

## Testing Strategy

### Test Types
- **Feature tests** - End-to-end booking flows, widget interactions
- **Unit tests** - Service classes, calculation logic
- **Integration tests** - Multi-tenant context testing

### Key Test Scenarios
- **Widget availability** - Booking validation logic
- **Payment processing** - Complete payment flows
- **Admin operations** - CRUD operations across tenants
- **Pricing calculations** - Seasonal and service pricing
- **State transitions** - Booking lifecycle testing

### Test Data
- **Factories** - Comprehensive model factories
- **Seeders** - Consistent test data setup
- **Tenant context** - Multi-tenant testing patterns

=== security rules ===

## Security Considerations

### Multi-Tenant Security
- **Data isolation** - Complete tenant data separation
- **Domain validation** - Prevent cross-tenant access
- **Resource scoping** - Automatic tenant scoping in queries

### Authentication
- **Central auth** - Superadmin authentication
- **Tenant auth** - Per-tenant user management
- **Two-factor auth** - Optional 2FA for admins

### Authorization
- **Role-based access** - Admin vs client permissions
- **Property-level access** - Multi-property tenant support
- **Resource policies** - Laravel policies for fine-grained control

=== performance rules ===

## Performance Optimization

### Caching Strategy
- **Tenant-scoped cache** - Automatic cache isolation
- **Season caching** - Seasonal pricing data caching
- **Availability caching** - Booking availability caching

### Database Optimization
- **Eager loading** - Prevent N+1 queries in booking operations
- **Indexing** - Proper indexes on frequently queried columns
- **Query optimization** - Efficient tenant-scoped queries

### Frontend Performance
- **Lazy loading** - Component lazy loading where appropriate
- **Asset optimization** - Vite bundling with code splitting
- **API efficiency** - Minimize requests in booking flow

=== deployment rules ===

## Deployment & Operations

### Environment Setup
- **Laravel Herd** - Local development server
- **Domain configuration** - Proper domain setup for multi-tenancy
- **Database provisioning** - Automated tenant database creation

### Monitoring
- **Tenant-specific logs** - Isolated logging per tenant
- **Performance monitoring** - Response times, database queries
- **Error tracking** - Centralized error monitoring

### Backup Strategy
- **Tenant-level backups** - Individual tenant database backups
- **Central data backup** - Tenant metadata and domains
- **Automated backups** - Scheduled backup processes

=== development rules ===

## Development Workflow

### Code Quality
- **Laravel Pint** - Automated code formatting
- **PHPStan** - Static analysis (if configured)
- **Prettier/ESLint** - Frontend code quality

### Git Workflow
- **Feature branches** - Feature-specific development
- **Code review** - Pull request reviews
- **Testing** - Automated testing in CI/CD

### Documentation
- **Inline documentation** - PHPDoc blocks for complex logic
- **Architecture docs** - Key architectural decisions
- **API docs** - Public API documentation

This rules file should be updated as the application evolves and new patterns emerge.