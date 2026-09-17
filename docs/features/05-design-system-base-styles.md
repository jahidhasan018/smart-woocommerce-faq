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
- [x] e2e: expand-all opens every item, then closes every item on the next press.

## Open questions
None.

## Test plan
- Unit: AccordionRenderer (9) — markup, aria attrs, expand-all on/off/default, unique ids, control/accordion association; DisplayEngine (5) — enqueue_assets.
- E2E: accordion.spec — renders, toggles open/closed, expand-all both ways, control/item sync.
- JS/CSS lint: wp-scripts lint-js + lint-style clean.

## Decisions made while building this
- Frontend styling ships as compiled CSS in `assets/build/` (gitignored); source in `assets/src/`.
- e2e tests target a standalone shortcode page (`/faq-test/`) because default block themes don't render WooCommerce classic product tabs (feature 23 will handle block-theme product surfaces).
- `typescript` pinned to 5.6.3 (7.x broke `@typescript-eslint`).
- PHPStan WC symbols via the lightweight `phpstan-wc.php` stub (see ADR-010).

## Design + behaviour pass
- **Fixed: the expand-all control never worked.** It rendered as a *sibling* of `.wsfq-accordion`, but `bindExpandAll()` looked it up with `accordion.querySelector('[data-wsfq-expand-all]')` — a descendant query — so it was never bound. The controller now scans for controls independently and targets its accordion through `aria-controls`. It flips both ways (expand, then collapse) by reading `aria-expanded` instead of assuming.
- **Fixed: the control did not render for shortcodes or the block.** `DisplayEngine` passed `expand_all`, but `ShortcodeRegistry` and `FaqBlock` call the renderer directly and passed nothing, and the renderer's fallback was `! empty( $args['expand_all'] )` = false. The renderer now resolves the saved setting itself when the caller passes nothing; an explicit argument still wins.
- **Fixed: Enter/Space toggled twice.** The JS handled them manually on top of the browser's native button activation. Removed; native behaviour is used.
- Each accordion now gets a unique DOM id (`wsfq-accordion-N`) so two accordions on one page keep their controls pointing at the right target.
- Icons: chevrons are inlined as SVG `mask-image` with `background-color: currentcolor`, so they inherit text colour, need no extra request, and can be rotated by CSS. The question chevron rotates 180° off `aria-expanded` (so it is always in step, however the item was opened); the expand-all control uses a double-chevron that flips and swaps its label to "Collapse all".
- The visible label swap and icon rotation are both CSS driven off `aria-expanded`, so no JS touches the text.
- Styling: larger radius, soft shadow, 15px/600 questions, roomier rows, `:hover` and `:focus-visible` states, open row tinted with a brand-coloured question, and a short reveal on the answer. All transitions and the reveal are disabled under `prefers-reduced-motion`.
- The control stays in step when items are toggled individually, so it never claims to expand when everything is already open.
