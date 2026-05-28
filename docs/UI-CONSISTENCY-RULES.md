# Manake UI Consistency & Theme Guidelines

This document outlines the visual consistency and theme rules established to protect the premium cinematic design of Manake.

## 1. Single Source of Truth: Dark Mode
* **Dark Mode** is the **master reference design**.
* Changing the theme must **NOT** change the layout structure, grid columns, spacing, radius, button dimensions, typography scale, or proportions.
* **Light Mode** is only a **color-adapted version** of dark mode. Both modes must share exactly the same DOM structure.

## 2. Reusable Semantic CSS Variables (Tokens)
All new components and styles must exclusively rely on these variables defined in `app.css` to enable automatic theme switching without manual inline ternary overrides:

* `--manake-bg`: Main site background (#0A0A0B in dark, warm off-white in light)
* `--manake-surface`: Main elevated surface background (#111113 in dark, pure white in light)
* `--manake-surface-muted`: Subtle overlay tint for cards and secondary controls
* `--manake-border`: Default layout boundary borders
* `--manake-text`: Primary high-contrast text color
* `--manake-text-muted`: Secondary soft text color
* `--manake-accent`: Theme signature accent (**Manake Gold `#D4A843`** in dark, **Manake Blue `#2563EB`** in light)
* `--manake-button-text`: Button text overlay color (#0A0A0B in dark, #FFFFFF in light)

## 3. Brand Logo Dimensions
* Logo bounding containers are strictly capped to prevent shifting:
  * **Navbar Logo wrapper**: `height: 44px; width: 180px;`
  * **Footer Logo wrapper**: `height: 36px; width: 160px;`
* Visual scale compensation (such as `scale(1.65)` in light mode blue logo) must be contained strictly inside the named component wrapper classes. Never use manual `h-*` or inline transform styles directly in templates.

## 4. Translation & Copy Integrity
* **Every static string** must be resolved through translation keys (`__('ui...')` or `__('app...')`).
* Never hardcode mixed Indonesian or English text in markup.
* For database settings fallbacks, use **locale-aware conditional overrides** in the PHP controller or view block so that saved Indonesian settings values do not override English fallback files.
