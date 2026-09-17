---
name: design-system
description: Points at the Phase 6 design tokens and the @wordpress/components UI language. States explicitly to match existing components before creating new ones.
---

# Design System

Match existing components before creating new ones. Never invent a visually inconsistent settings panel.

## Source of truth
- Design tokens live in `assets/src/scss/tokens.scss` — CSS custom properties (`--wsfq-color-primary`, `--wsfq-radius-*`, `--wsfq-space-*`, `--wsfq-font-*`) generated from Sass variables.
- Both stylesheets compile those tokens to custom properties on `:root`. Runtime customization (Settings → Design tab) writes an inline `<style>` block overriding these custom properties — no rebuild needed for a client brand color.
- Two separate stylesheets: `assets/src/admin/style.scss` (admin app, `wsfq-settings-*` / `wsfq-section` / `wsfq-setting` classes, sits inside `wp-admin` chrome) and `assets/src/scss/frontend.scss` (BEM, `wsfq-` prefixed, theme-agnostic, mobile-first).
- Admin assets are enqueued from `assets/build/style-admin.css` — wp-scripts names the stylesheet `style-<entry>.css`, not `<entry>.css`.

## UI primitives (admin)
Build with `@wordpress/components`: `TabPanel`, `ToggleControl`, `SelectControl`, `Notice`, `Button`, `Spinner`. Do not reintroduce `Card` in the admin app — the panel already provides the surface.

Layout conventions inside the settings panel:
- The panel itself is the surface: one bordered, rounded container holding a tinted tab strip and a padded content area.
- Sections group settings by consequence. Section headings are `h2`; the count badge sits outside the heading text so screen readers do not read it as part of the title.
- Toggle rows lead with the label and trail the toggle, so labels share one reading edge and controls share another. Rows inside a section are separated by hairlines; sections are separated by space and never by a line.
- Row labels stay at normal weight and the section heading is the only bold thing, so the hierarchy is legible at a glance. Prefer a label that names the storefront location ("Below the short description") over one that needs a help sentence to explain it.
- Use a mutex control for mutually-exclusive choices, never a set of toggles. A group of toggle rows implies any combination is valid; if only one option may be active, use `SelectControl` (see the explicit-set requirement in `src/Admin/SettingsService.php`).
- `SelectControl` renders its label uppercase and bold (11px/600). Override it to 13px/400, sentence case inside `.wsfq-field`, and put the field in a max-width column so it does not stretch the panel.
- Prefer `__nextHasNoMarginBottom` on form controls and control row spacing from the wrapper class.

## Frontend layout
- Accordion rows: Flexbox. Multi-column: CSS Grid. RTL via logical properties (`margin-inline`, etc.).
- The accordion is a single bordered, rounded surface; items are separated by hairlines and the last one drops its border. Questions are 15px/600 with roomy padding; the open item gets a tinted background and a brand-coloured question.
- Accessibility baked in: `aria-expanded`, `aria-controls`, keyboard operability (arrow keys / Home / End; Enter and Space come free from the native `<button>`, so never bind them manually or the item toggles twice).

## Icons
- Icons are inlined SVG `mask-image` (not `<img>`, not an icon font) with `background-color: currentcolor`, so they inherit text colour, need no extra request, and can be transformed by CSS. Declare them as Sass variables at the top of `frontend.scss` (`$wsfq-icon-*`).
- Directional icons rotate off `aria-expanded` in CSS, never by JS swapping the element. The question chevron rotates 180°; the expand-all double-chevron flips and its visible label swaps between "Expand all" and "Collapse all" via two spans where the second is `display: none` until expanded.
- Never show state by colour alone — pair every state change with the icon and/or the label.

## Motion
- Short and functional only: a 150ms colour/background transition and a 250ms icon rotation, plus a small fade+rise on the revealed answer.
- Every transition and animation is disabled under `prefers-reduced-motion: reduce`. Test it, do not assume it.

## Screenshots
The settings dashboard exists (Phase 4 / feature 08): a Gutenberg-native React app at `admin.php?page=wsfq-smart-faq`. Capture + embed real screenshots here so new sessions match this visual language automatically — do not invent a different panel style.
