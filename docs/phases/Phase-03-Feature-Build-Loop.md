# Phase 3 — Feature Build Loop


**Skills to load:** `wp-plugin-development` (base for every feature) + the feature-specific skill(s) below

Every feature from here on follows the same loop, one at a time, never in parallel:

1. Create `docs/features/NN-name.md`, fill in Goal + Acceptance Criteria.
2. Branch `feature/NN-name` off `develop`.
3. Write failing tests (unit first, integration where needed).
4. Implement the minimum code to pass.
5. Refactor for SOLID compliance.
6. Run `composer cs && composer stan && composer test`.
7. Add/update Playwright e2e coverage if it's user-facing.
8. Update `docs/features/NN-name.md` (status → Done, note any decisions) and `docs/PROGRESS.md`.
9. Open PR → CI green → merge to `develop`.

**Recommended feature order** (mapped from your feature list — build order optimizes for "each layer unblocks the next" and lets you demo something real early):

| # | Feature | Depends on |
|---|---|---|
| 01 | Core CPT/taxonomy skeleton | Phase 2 |
| 02 | FAQ assignment (product / category / tag / variation / global) — also wire the duplicate/clone helper from Phase 2 into the library and product-edit UI | 01 |
| 03 | Display engine: hooks for all product/shop/cart/checkout positions | 02 |
| 04 | Shortcodes (`[wsf_all]`, `[wsf_product]`, etc.) | 03 |
| 05 | Design system + base accordion styles (Phase 6) — includes the expand-all / collapse-all control | 03 |
| 06 | Gutenberg block (Prebuilt + Custom) | 04, 05 |
| 07 | Google FAQPage JSON-LD schema | 03 |
| 08 | Settings dashboard shell + tabs (Phase 4) | 05 |
| 09 | Product-edit FAQ meta box, manual entry (no AI yet) — supports media in answers (images, video, embedded HTML); includes optional comments-on-FAQs toggle | 02, 08 |
| 10 | AI provider abstraction + single-provider generation (Phase 5) | 09 |
| 11 | Multi-provider + failover, tone/templates | 10 |
| 12 | Bulk AI generation (category-wide) via Action Scheduler | 11 |
| 13 | Inline search | 04 |
| 14 | AJAX library + per-product search | 13 |
| 15 | Hash deep-linking | 04 |
| 16 | Customer Q&A submission + moderation — includes email notifications on new question / new answer and dynamic product-attribute placeholders (`{product_price}`, `{stock_status}`) in answers | 09 |
| 17 | AI auto-draft answers for customer questions | 10, 16 |
| 18 | FAQ engagement analytics | 03 |
| 19 | Import/export (JSON) | 01–08 stable |
| 20 | Migration importer from competitor plugins | 19 |
| 21 | REST API (public + admin) | 08 |
| 22 | Elementor / Divi / Bricks integrations — Bricks is a deliberate differentiator competitors don't offer; call it out in marketing/readme | 06 |
| 23 | WooCommerce Blocks (Cart/Checkout block templates) compatibility | 03 |
| 24 | WPML / Polylang | stable core |
| 25 | Multisite network options — includes the network-wide shared FAQ library option | stable core |
| 26 | Developer hooks/filters documentation pass | all above |
| 27 | Accessibility + RTL polish pass | all display work |

Each row becomes its own `docs/features/NN-*.md` file, created only when you're about to start it (don't pre-write 27 empty specs — that's busywork, not planning).

**Feature → skill map** (load only the relevant one, on top of `wp-plugin-development`):

| Feature(s) | Skill(s) |
|---|---|
| 05, 06, 08, 22, 23, 27 (frontend UI, blocks, editors) | `wp-block-development`, `wpds` |
| 07 (schema), 14, 16, 17 (AJAX/search/Q&A) | `wp-rest-api` |
| 21 (REST API) | `wp-rest-api` |
| 10, 11, 12 (AI providers/generation) | `wp-rest-api`, `wp-phpstan` |
| 18 (analytics) | `wp-performance` |
| 19, 20 (import/export/migration) | `wp-wpcli-and-ops`, `wp-plugin-development` |
| 24 (WPML/Polylang), 25 (multisite) | `wp-wpcli-and-ops` |
| Any feature with WP-CLI commands | `wp-wpcli-and-ops` |
| All (final QA) | `wp-plugin-directory-guidelines`, `wp-performance` |

---

