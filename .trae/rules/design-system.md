---
description: Design rules to transform Laravel Vue Starter Kit into Supabase Industrial UI
globs: **/*.vue, **/*.css
---

# 🎨 DESIGN SYSTEM: Supabase Industrial (Starter Kit Edition)

You are a UI/UX Expert. Your goal is to adapt the existing Laravel Vue Starter Kit components to match the **Supabase / Linear** aesthetic.

## 1. VISUAL TRANSFORMATION RULES
The Starter Kit defaults to "Soft UI". You must enforce "Industrial UI":

* **Shadows -> Borders:**
    * *Default Kit:* Uses `shadow-sm` or `shadow-md`.
    * *Supabase:* REMOVE shadows. Use `border border-border` instead.
    * *Rule:* Depth is defined by borders, not shadows.

* **Radius (Sharpness):**
    * *Default Kit:* Often `rounded-lg` or `rounded-xl`.
    * *Supabase:* Use `rounded-md` (max) or `rounded-sm`. The UI should feel precise and engineered.

* **Backgrounds:**
    * Avoid pure white backgrounds for cards. Use `bg-card` (which should be slightly lighter than the main background in dark mode).

## 2. COMPONENT OVERRIDES (Specific to Starter Kit)
When using or modifying standard kit components, apply these changes:

* **`PrimaryButton.vue`:**
    * Make it adhere to the Brand Color (Green/Teal for Supabase look, or your brand).
    * Add `shadow-none` and `font-medium`.
    * Ensure height is compact (`h-9` usually).

* **`TextInput.vue`:**
    * Remove default heavy focus rings.
    * Use `border-input bg-background focus:ring-1 focus:ring-ring focus:border-ring`.
    * Placeholder should be `text-muted-foreground`.

* **`Modal.vue`:**
    * Remove `rounded-2xl`. Use `rounded-lg`.
    * Ensure the border is visible: `border border-border`.

## 3. LAYOUT PATTERNS (Dashboard)
* **Sidebar:** Use a fixed sidebar with `border-r`.
* **Topbar:** Use a slim topbar with `border-b` and `bg-background/95 backdrop-blur`.
* **Content:** The main content area should be `bg-background` (usually Zinc-950 in dark mode).

## 4. TYPOGRAPHY
* Headings: `tracking-tight font-semibold`.
* Table Headers: `uppercase text-xs font-mono text-muted-foreground`.