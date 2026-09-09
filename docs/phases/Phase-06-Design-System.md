# Phase 6 — Design System


**Skills to load:** `wpds`, `wp-block-development`

- Use **`@wordpress/scripts`' built-in Sass support** (import `.scss` directly from your JS entry points — no extra Sass toolchain to configure or maintain) for both admin and frontend styles. This is "something that already ships with the WordPress ecosystem," as you asked for.
- `assets/src/scss/tokens.scss` — CSS custom properties (`--wsfq-color-primary`, `--wsfq-radius`, `--wsfq-space-*`, `--wsfq-font-*`), generated once from Sass variables. Runtime customization (colors chosen in Settings → Design tab) writes an inline `<style>` block overriding these custom properties — no rebuild needed for a client to pick a brand color.
- Two separate stylesheets, never one mega-file:
  - `admin.scss` — deliberately inherits WP admin's own design tokens where possible (don't fight `wp-admin` styling).
  - `frontend.scss` — fully scoped (BEM, `wsfq-` prefixed classes), theme-agnostic, mobile-first, Flexbox for the accordion rows, CSS Grid for the multi-column layout option, logical properties (`margin-inline`, etc.) for RTL support.
- Accessibility baked in from the start: `aria-expanded`, `aria-controls`, keyboard operability (Enter/Space toggles, arrow-key navigation between questions) — not a "polish pass" afterthought, since retrofitting ARIA into already-built markup is exactly the kind of rework TDD-for-UI is meant to avoid.
- Expand-all / collapse-all control — a frontend toggle that expands/collapses every FAQ on the page; exposed as a render option and via a `wsfq_` hook so it can be positioned or hidden.

---

