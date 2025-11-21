# Project Handoff: Rezio Booking System

**Last Updated:** 2025-11-21  
**Project:** Laravel/Vue.js Booking Management System  
**Status:** Phase 1 Complete, Phase 2 In Progress

---

## 🎯 Current State

### ✅ Completed Features (Phase 1)

1. **Core Infrastructure**
   - Laravel 12 + Vue 3 + Inertia.js + Shadcn UI + TailwindCSS
   - User authentication (Laravel Fortify)
   - Database structure (properties, bookings)
   - Ziggy for route management

2. **Property Management**
   - Full CRUD for properties
   - Properties linked to authenticated users

3. **Booking System**
   - Public booking widget (accessible via `widget_token`)
   - Admin booking management with status filtering
   - Calendar view with VCalendar
   - Block dates functionality
   - Edit/Delete bookings from calendar
   - Search bookings by guest name/email
   - Export bookings to CSV
   - Email notifications (BookingConfirmation, NewBookingAlert)

4. **Booking Notes**
   - Internal notes field for admins
   - Visible in Dashboard edit modal and Bookings table
   - Included in CSV exports

5. **Seasonal Pricing** ✨
   - `seasonal_prices` table and model
   - `SeasonalPriceController` with overlap validation
   - Dynamic price calculation in `BookingWidgetController`
   - Management UI in Property settings
   - Fully tested with `SeasonalPricingTest`

6. **Reporting Dashboard** 📊
   - `ReportController` with revenue, occupancy, and trend calculations
   - Interactive dashboard with Chart.js
   - Date range and property filtering
   - Fully tested with `ReportingTest`

---

## 🚀 Next Steps (Phase 2)

### Immediate Priority: Automated Reminders

**Goal:** Automatically send email reminders to guests before their check-in date.

**Key Tasks:**
1. Create `BookingReminder` Mailable.
2. Create `SendBookingReminders` Command.
3. Schedule command in `routes/console.php`.
4. Add `reminders_sent_at` column to bookings table (to prevent duplicates).

### Remaining Phase 2 Features (in order)

1. **Cleaning Schedule** - Auto-create cleaning tasks between bookings
2. **GoPay Integration** - Payment processing (requires merchant account)

---

## 📁 Project Structure

```
/Users/jakub/Work/Laravel/rezio/
├── app/
│   ├── Http/Controllers/
│   │   ├── BookingController.php       # CRUD + search + export + notes
│   │   ├── BookingWidgetController.php # Public booking form + seasonal pricing
│   │   ├── DashboardController.php     # Calendar view with bookings
│   │   ├── PropertyController.php      # Property CRUD
│   │   ├── SeasonalPriceController.php # NEW - Seasonal pricing CRUD
│   ├── Models/
│   │   ├── Booking.php                 # includes 'notes' field
│   │   ├── Property.php
│   │   ├── SeasonalPrice.php           # NEW
│   │   └── User.php
│   ├── Mail/
│   │   ├── BookingConfirmation.php
│   │   └── NewBookingAlert.php
├── database/
│   ├── factories/
│   │   ├── BookingFactory.php
│   │   ├── PropertyFactory.php
│   │   └── SeasonalPriceFactory.php    # NEW
│   ├── migrations/
│   │   ├── 2025_11_21_080622_add_notes_to_bookings_table.php
│   │   └── 2025_11_21_081407_create_seasonal_prices_table.php # NEW
├── resources/js/
│   ├── pages/
│   │   ├── Dashboard.vue               # Calendar + Block Dates + Edit Booking
│   │   ├── Bookings/Index.vue          # Search + Export + Notes column
│   │   ├── Properties/                 # CRUD views
│   │   ├── SeasonalPrices/Index.vue    # NEW - Manage seasonal prices
│   │   └── Widget/Show.vue             # Public booking form
│   ├── components/ui/                  # Shadcn components
│   └── layouts/
│       ├── AppLayout.vue               # Admin layout with sidebar
│       └── WebLayout.vue               # Public layout
├── routes/
│   └── web.php                         # All routes defined here
└── tests/Feature/
    ├── BookingNotesTest.php
    ├── BookingSearchExportTest.php
    ├── BlockDatesTest.php
    ├── CalendarManagementTest.php
    └── SeasonalPricingTest.php         # NEW - 3 passing tests
```

---

## 🔑 Key Files to Know

### Backend Controllers

**BookingController** (`app/Http/Controllers/BookingController.php`)
- `index()` - List bookings with search/filter
- `store()` - Create blocked dates
- `update()` - Edit booking (status, dates, notes) with overlap validation
- `destroy()` - Delete booking
- `export()` - CSV export with notes
- `hasOverlap()` - Private method for overlap checking

**SeasonalPriceController** (`app/Http/Controllers/SeasonalPriceController.php`)
- `index()` - List seasonal prices
- `store()` / `update()` - Create/Edit with overlap validation
- `destroy()` - Delete price

**BookingWidgetController** (`app/Http/Controllers/BookingWidgetController.php`)
- `calculateTotalPrice()` - Calculates price based on base rate and seasonal overrides

### Frontend Components

**SeasonalPrices/Index.vue** (`resources/js/pages/SeasonalPrices/Index.vue`)
- Table listing seasonal prices
- Modal for Add/Edit
- Validation error display

---

## 🗄️ Database Schema

### `seasonal_prices` table
```sql
- id
- property_id (foreign key)
- name (string)
- start_date (date)
- end_date (date)
- price_per_night (decimal)
- timestamps
```

---

## 🧪 Testing

**Run all tests:**
```bash
php artisan test
```

**Run specific test:**
```bash
php artisan test tests/Feature/SeasonalPricingTest.php
```

**Current test coverage:**
- ✅ BlockDatesTest
- ✅ CalendarManagementTest
- ✅ BookingSearchExportTest
- ✅ BookingNotesTest
- ✅ SeasonalPricingTest

---

## 🛠️ Development Commands

**Start dev server:**
```bash
npm run dev
php artisan serve
```

**Run migrations:**
```bash
php artisan migrate
```

---

## 🐛 Known Issues / Technical Debt

### TypeScript Lint Errors (Non-blocking)
- Several `Undefined method 'id'` errors in controllers (Intelephense false positives)
- `Cannot find name 'route'` in some Vue files (fixed in most, remaining are cosmetic)
- `Cannot find module '@/components/ui/select'` (Shadcn UI type declaration issue)

**These don't affect functionality** - the app runs correctly. Can be addressed later with proper PHPDoc annotations and TypeScript declarations.

---

## 📝 Important Conventions

### Laravel Boost Guidelines
This project follows Laravel Boost guidelines (see `GEMINI.md`):
- Use `search-docs` tool for Laravel ecosystem documentation
- Always create tests for new features
- Use `php artisan make:` commands for new files
- Follow existing code conventions (check sibling files)
- Use Eloquent relationships over raw queries

### Frontend
- Shadcn UI for all components
- Inertia.js for routing (no traditional Blade views)
- Ziggy for route helpers: `route('bookings.index')`
- TypeScript with Vue 3 Composition API

### Testing
- Use factories for test data
- Feature tests over unit tests
- Test both happy path and edge cases

---

## 🎯 To Continue Development

1. **Review the implementation plan:**
   ```bash
   cat /Users/jakub/.gemini/antigravity/brain/514c1031-86b4-41ed-a329-fa2e6fe2fb39/implementation_plan.md
   ```

2. **Check current task status:**
   ```bash
   cat /Users/jakub/.gemini/antigravity/brain/514c1031-86b4-41ed-a329-fa2e6fe2fb39/task.md
   ```

3. **Start with Reporting Dashboard:**
   - Install Chart.js: `npm install chart.js vue-chartjs`
   - Create `ReportController`
   - Create `Reports/Index.vue`
   - Implement charts and filters

---

## 📞 Context for AI Assistant

When resuming this project, tell the AI:

> "I'm continuing the Rezio booking system. We just completed Seasonal Pricing. Please review `/Users/jakub/Work/Laravel/rezio/HANDOFF.md` and `/Users/jakub/.gemini/antigravity/brain/514c1031-86b4-41ed-a329-fa2e6fe2fb39/implementation_plan.md`. I want to implement the Reporting Dashboard next."

Or simply:

> "Continue with the next feature from the implementation plan."

---

## 🔗 Useful Links

- **Laravel Docs:** https://laravel.com/docs/12.x
- **Inertia.js:** https://inertiajs.com
- **Shadcn Vue:** https://www.shadcn-vue.com
- **VCalendar:** https://vcalendar.io
- **Chart.js:** https://www.chartjs.org

---

**Good luck! The foundation is solid and well-tested. 🚀**
