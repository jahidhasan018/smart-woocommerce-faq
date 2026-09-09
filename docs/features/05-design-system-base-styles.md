---
status: done
branch: feature/05-design-system-base-styles
---

## 05. Design system + base accordion styles

## Goal
Provide the frontend design system (Sass tokens + scoped styles) and an accessible accordion with an expand-all / collapse-all control, wired through the build pipeline and enqueued on the frontend.

## Acceptance criteria
- [x] `assets/src/scss/tokens.scss` — design tokens compiled to CSS custom properties (`--wsfq-*`).
- [x] `assets/src/scss/frontend.scss` — BEM, `wsfq-` prefixed, theme-agnostic, mobile-first, Flexbox rows, logical properties (RTL), CSS Grid columns variant.
- [x] `assets/src/js/accordion.js` + `index.js` — toggle on click, Enter/Space, arrow-key nav, Home/End, expand-all/collapse-all.
- [x] `webpack.config.js` — wp-scripts entry → `assets/build/`.
- [x] Renderer emits accessible markup: `<button>` toggles with `aria-expanded`/`aria-controls`, `<h3>` questions, `hidden` answers.
- [x] Expand-all / collapse-all control renders via a render arg + `wsfq_accordion_expand_all` filter; defaults on (`wsfq_expand_all` option).
- [x] `DisplayEngine::enqueue_assets()` loads the built JS + CSS from the asset manifest.
- [x] e2e: accordion toggles open/closed on a real page.

## Open questions
None.

## Test plan
- Unit: AccordionRenderer (5) — markup, aria attrs, expand-all on/off; DisplayEngine (5) — enqueue_assets.
- E2E: accordion.spec — renders, toggles open/closed via real browser.
- JS/CSS lint: wp-scripts lint-js + lint-style clean.

## Decisions made while building this
- Frontend styling ships as compiled CSS in `assets/build/` (gitignored); source in `assets/src/`.
- e2e tests target a standalone shortcode page (`/faq-test/`) because default block themes don't render WooCommerce classic product tabs (feature 23 will handle block-theme product surfaces).
- `typescript` pinned to 5.6.3 (7.x broke `@typescript-eslint`).
- PHPStan WC symbols via the lightweight `phpstan-wc.php` stub (see ADR-010).