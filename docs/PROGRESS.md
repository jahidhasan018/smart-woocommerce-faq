# Progress

Single source of truth for feature status. Updated at the start and end of every feature. Status values: `Not started / In progress / Blocked / Needs tests / Done`.

## Feature list (from plan.md Phase 3)

| # | Feature | Status | Branch | Notes |
|---|---|---|---|---|
| 01 | Core CPT/taxonomy skeleton | Done | feature/01-core-cpt-taxonomy-skeleton | wsfq_faq CPT + category/group taxonomies; tests green |
| 02 | FAQ assignment (product / category / tag / variation / global) | Done | feature/02-faq-assignment | postmeta storage + FaqResolver; tests green |
| 03 | Display engine: hooks for all product/shop/cart/checkout positions | Not started | — | depends on 02 |
| 04 | Shortcodes (`[wsfq_all]`, `[wsfq_product]`, etc.) | Not started | — | depends on 03 |
| 05 | Design system + base accordion styles | Not started | — | depends on 03 |
| 06 | Gutenberg block (Prebuilt + Custom) | Not started | — | depends on 04, 05 |
| 07 | Google FAQPage JSON-LD schema | Not started | — | depends on 03 |
| 08 | Settings dashboard shell + tabs | Not started | — | depends on 05 |
| 09 | Product-edit FAQ meta box, manual entry | Not started | — | depends on 02, 08 |
| 10 | AI provider abstraction + single-provider generation | Not started | — | depends on 09 |
| 11 | Multi-provider + failover, tone/templates | Not started | — | depends on 10 |
| 12 | Bulk AI generation (category-wide) via Action Scheduler | Not started | — | depends on 11 |
| 13 | Inline search | Not started | — | depends on 04 |
| 14 | AJAX library + per-product search | Not started | — | depends on 13 |
| 15 | Hash deep-linking | Not started | — | depends on 04 |
| 16 | Customer Q&A submission + moderation | Not started | — | depends on 09 |
| 17 | AI auto-draft answers for customer questions | Not started | — | depends on 10, 16 |
| 18 | FAQ engagement analytics | Not started | — | depends on 03 |
| 19 | Import/export (JSON) | Not started | — | depends on 01–08 stable |
| 20 | Migration importer from competitor plugins | Not started | — | depends on 19 |
| 21 | REST API (public + admin) | Not started | — | depends on 08 |
| 22 | Elementor / Divi / Bricks integrations | Not started | — | depends on 06 |
| 23 | WooCommerce Blocks (Cart/Checkout) compatibility | Not started | — | depends on 03 |
| 24 | WPML / Polylang | Not started | — | stable core |
| 25 | Multisite network options | Not started | — | stable core |
| 26 | Developer hooks/filters documentation pass | Not started | — | all above |
| 27 | Accessibility + RTL polish pass | Not started | — | all display work |

## Build phases (from Planing/plan.md)

| Phase | Status | Branch | Notes |
|---|---|---|---|
| 00 | Guiding Principles | Done | — | applies to every phase |
| 01 | Foundation: Environment, Tooling & AI-Agent Context | Done | develop | wp-env, composer/npm tooling, PHPCS/PHPStan, PHPUnit 10 + 9.6-phar split, Playwright, CI, git hooks, prefix → wsfq, skills gitignored |
| 02 | Core Architecture Skeleton (walking skeleton) | Done | develop | Plugin singleton, Activator/Deactivator/Upgrader, wsfq_faq CPT + taxonomies, clone helper — all green |
| 03 | Feature Build Loop (features 01–27) | Not started | — | see feature table above |
| 04 | Settings Dashboard | Not started | — | depends on feature 05, 08 |
| 05 | Product-Edit Screen: FAQ Panel + AI Generation | Not started | — | depends on features 09–11 |
| 06 | Design System | Not started | — | depends on feature 05 |
| 07 | Database, Caching & Performance | Not started | — | — |
| 08 | Security & REST API | Not started | — | — |
| 09 | Versioning, Update Safety & Migration System | Not started | — | — |
| 10 | WP.org Submission Readiness | Not started | — | — |
| 11 | Post-Launch Operations | Not started | — | — |

When a phase is done, update its status here (and the matching `/docs/phases/` file if its content changed).