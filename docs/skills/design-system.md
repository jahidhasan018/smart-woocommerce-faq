---
name: design-system
description: Points at the Phase 6 design tokens and the @wordpress/components UI language. States explicitly to match existing components before creating new ones.
---

# Design System

Match existing components before creating new ones. Never invent a visually inconsistent settings panel.

## Source of truth
- Design tokens live in `assets/src/scss/tokens.scss` — CSS custom properties (`--wsfq-color-primary`, `--wsfq-radius`, `--wsfq-space-*`, `--wsfq-font-*`) generated from Sass variables.
- Runtime customization (Settings → Design tab) writes an inline `<style>` block overriding these custom properties — no rebuild needed for a client brand color.
- Two separate stylesheets: `admin.scss` (inherits `wp-admin` tokens) and `frontend.scss` (BEM, `wsfq-` prefixed, theme-agnostic, mobile-first).

## UI primitives (admin)
Build with `@wordpress/components`: `TabPanel`, `Card`/`CardBody`, `ToggleControl`, `SelectControl`, `Notice`, `Button`. Using the same primitives as Jetpack keeps the UI visually consistent.

## Frontend layout
- Accordion rows: Flexbox. Multi-column: CSS Grid. RTL via logical properties (`margin-inline`, etc.).
- Accessibility baked in: `aria-expanded`, `aria-controls`, keyboard operability (Enter/Space toggles, arrow-key navigation).

## Screenshots
The settings dashboard exists (Phase 4 / feature 08): a Gutenberg-native React app at `admin.php?page=wsfq-smart-faq` using `@wordpress/components` (`TabPanel`, `Card`, `ToggleControl`, `Notice`, `Button`). Capture + embed real screenshots here so new sessions match this visual language automatically — do not invent a different panel style.