# Global Design Refactor Checklist - Supabase Industrial

## Core Components (resources/js/Components)

### Buttons (PrimaryButton.vue / ui/button)
- [x] Remove `shadow-md`, `shadow-lg` from all variants.
- [x] Add `shadow-sm` to interactive variants (default, outline, secondary) for subtle lift.
- [x] Ensure `h-9` is the default height.
- [x] Use `rounded-md` (not xl/2xl).
- [x] Ensure borders are present (`border-input` or `border-primary`).

### Inputs (TextInput.vue / ui/input)
- [x] Replace `border-gray-300` with `border-input`.
- [x] Use `bg-background`.
- [x] Add `shadow-sm`.
- [x] Ensure `h-9` height.
- [x] Remove glow effects, use `ring-1` on focus.

### Cards & Modals (ui/card, ui/dialog, ui/sheet)
- [x] Use `bg-card` and `text-card-foreground`.
- [x] Add `border border-border`.
- [x] Remove `shadow-lg`, `shadow-xl`.
- [x] Use `rounded-lg` (max) or `rounded-md`.
- [x] Ensure no inner shadows on headers.

### Layouts
- [x] **AppLayout / Sidebar:**
    - [x] Top Navigation: `border-b border-border`, `bg-background`, no shadow.
    - [x] Sidebar: `border-r border-border`, `bg-sidebar`.
    - [x] Sidebar Header: Reduced height to `h-12` (from `h-14`) for compactness.
- [x] **Page Content:**
    - [x] Consistent `bg-background` (or transparent if handled by wrapper).
    - [x] Full-width containers (Removed `max-w-7xl` from `GuestLayout.vue` and `Admin/Properties/Bookings/Show.vue`).

## Global Pages Audit

### Auth Pages
- [x] `Login.vue`: Check for shadows on cards. (Verified `AuthSimpleLayout` uses `border` and `bg-card`, no shadow).
- [x] `Register.vue`: Same as Login.

### Guest Pages
- [x] `Guest/CheckIn/Show.vue`: Removed `rounded-lg`, fixed `bg-muted` usage. Removed `max-w-7xl` from layout.

### Admin Pages
- [x] `Dashboard.vue`: Check grid spacing and card styles. (Done).
- [x] `Properties/Index.vue`: Button sizes `h-9`, flat table styles. (Done).
- [x] `Properties/Edit.vue` & `Create.vue`: `rounded-xl` removal. (Done).
- [x] `Settings/Appearance.vue`: Remove `shadow-sm`. (Done).
- [x] `Widget/Index.vue` & partials: Remove `shadow-lg`, `rounded-xl`, fix borders. (Done).
- [x] `Properties/Bookings/Show.vue`: Removed `max-w-7xl`, fixed `h-10` Avatar to `h-9`.

## Typography & Colors
- [x] Use `text-muted-foreground` for secondary text.
- [x] Table headers: `text-xs uppercase tracking-wider font-mono text-muted-foreground`.
- [x] Headings: `tracking-tight font-semibold text-foreground`.

## Verification
- [x] Grep for `rounded-xl`, `rounded-2xl`, `rounded-3xl` in `resources/js/Pages`. (Result: Clean).
- [x] Grep for `shadow-md`, `shadow-lg`, `shadow-xl` in `resources/js/Pages`. (Result: Clean).
- [x] Grep for `shadow-sm` and verify it's only on Buttons/Inputs. (Result: Cleaned up exceptions).
- [x] Enforced `h-9` on all Button sizes (default, sm, lg, icon) in `ui/button/index.ts`.
