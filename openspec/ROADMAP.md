# Roadmap

Single source of truth for build status. Updated at the start and end of every phase/feature. Status values: `Not started / In progress / Blocked / Needs tests / Done`.

Durable product requirements live in `openspec/specs/`. This table tracks delivery status; driven work is tracked as OpenSpec changes.

## Phases (from the build plan; this proposal is the adoption vehicle)

| Phase | Status | Notes |
|---|---|---|
| 00 | Guiding Principles | Done | applies to every phase |
| 01 | Foundation: Environment, Tooling & AI-Agent Context | Done | wp-env, tooling, PHPCS/PHPStan, PHPUnit split, Playwright, CI, git hooks, wsfq prefix, skills gitignored |
| 02 | Core Architecture Skeleton (walking skeleton) | Done | Plugin singleton, lifecycle, wsfq_faq CPT + taxonomies, clone helper |
| 03 | Feature Build Loop (features 01–27) | In progress | see feature table below |
| 04 | Settings Dashboard | Done via feature 08 | see feature table |
| 05 | Product-Edit Screen: FAQ Panel + AI | Not started | depends on features 09–11 |
| 06 | Design System | Done via feature 05 | see feature table |
| 07 | Database, Caching & Performance | Not started | — |
| 08 | Security & REST API | Not started | addressed as part of feature 21 (rest-api spec) |
| 09 | Versioning, Update Safety & Migration System | Not started | — |
| 10 | WP.org Submission Readiness | Not started | — |
| 11 | Post-Launch Operations | Not started | — |

## Features

| # | Feature | Capability spec | Status | Notes |
|---|---|---|---|---|
| 01 | Core CPT/taxonomy skeleton | content-model | Done | wsfq_faq CPT + category/group taxonomies; tests green |
| 02 | FAQ assignment (product / category / tag / variation / global) | content-model | Done | postmeta storage + resolver; tests green |
| 03 | Display engine: hooks for all positions | display-surfaces | Done | renderer + 8 positions; tests green |
| 04 | Shortcodes | shortcodes | Done | 6 shortcodes; search deferred |
| 05 | Design system + base accordion styles | design-system | Done | tokens + accessible accordion + expand/collapse |
| 06 | Gutenberg block (Prebuilt) | gutenberg-block | Done | dynamic wsfq/faq block; Custom deferred |
| 07 | Google FAQPage JSON-LD schema | seo-schema | Done | FAQPage schema on product + FAQ pages |
| 08 | Settings dashboard shell + tabs | settings-dashboard | Done | admin React app + wsfq/v1/settings |
| 09 | Product-edit FAQ meta box, manual entry | product-edit-faq-panel | Not started | depends on 02, 08 |
| 10 | AI provider abstraction + single-provider generation | ai-generation | Not started | depends on 09 |
| 11 | Multi-provider + failover, tone/templates | ai-generation | Not started | depends on 10 |
| 12 | Bulk AI generation (category-wide) via Action Scheduler | ai-generation | Not started | depends on 11 |
| 13 | Inline search | search-discovery | Not started | depends on 04 |
| 14 | AJAX library + per-product search | search-discovery | Not started | depends on 13 |
| 15 | Hash deep-linking | search-discovery | Not started | depends on 04 |
| 16 | Customer Q&A submission + moderation | customer-q-a | Not started | depends on 09 |
| 17 | AI auto-draft answers for customer questions | ai-generation | Not started | depends on 10, 16 |
| 18 | FAQ engagement analytics | analytics | Not started | depends on 03 |
| 19 | Import/export (JSON) | data-portability | Not started | depends on 01–08 stable |
| 20 | Migration importer from competitor plugins | data-portability | Not started | depends on 19 |
| 21 | REST API (public + admin) | rest-api | Not started | depends on 08 |
| 22 | Elementor / Divi / Bricks integrations | builder-integrations | Not started | depends on 06 |
| 23 | WooCommerce Blocks (Cart/Checkout) compatibility | builder-integrations | Not started | depends on 03 |
| 24 | WPML / Polylang | internationalization-multisite | Not started | stable core |
| 25 | Multisite network options | internationalization-multisite | Not started | stable core |
| 26 | Developer hooks/filters documentation pass | content-model / display-surfaces / rest-api | Not started | all above |
| 27 | Accessibility + RTL polish pass | design-system | Not started | all display work |

When a phase or feature completes, update its status here and archive the corresponding OpenSpec change.