# Proposal

## Why

The build roadmap, feature catalog, progress tracking, architecture notes, and architecture decisions are spread across four overlapping systems (`Planing/plan.md`, `docs/phases/*.md`, `docs/features/*.md`, `docs/DECISIONS.md`, `docs/PROGRESS.md`). They duplicate the same story in different formats and drift. OpenSpec already models this with durable capability specs plus per-change proposals and tasks, so consolidating onto OpenSpec gives one validated, machine-checkable source of truth instead of hand-synced Markdown tables.

## What Changes

- Introduce durable capability specs under `openspec/specs/` capturing the product's behavior contract, consolidated from `feature-list.md` and the `docs/features/*.md` files.
- Reproduce the build roadmap and live status as OpenSpec artifacts: phases become tasks in this proposal's change, and feature status is tracked via open vs archived changes.
- Add `openspec/ROADMAP.md` as the living status table (replacing `docs/PROGRESS.md`), kept in sync with change open/archived state.
- Migrate architecture decisions into `openspec/DECISIONS.md` as ADRs (replacing `docs/DECISIONS.md`).
- **REMOVED**: `Planing/` and `docs/` directories once content is fully represented in `openspec/`.
- Keep `ARCHITECTURE.md`, `README.md`, `AGENTS.md`, `CONTRIBUTING.md` as pointer/reference docs that link into `openspec/`.

## Capabilities

### New Capabilities

- `content-model`: the `wsfq_faq` post type, FAQ categories/groups taxonomies, and product/category/tag/variation/global assignment model (features 01, 02; feature-list 1).
- `display-surfaces`: strategy-based rendering of FAQs across all WooCommerce positions (product, shop, cart, checkout) with hook-based placement (feature 03; feature-list 2).
- `shortcodes`: the `wsfq_faq_*` shortcode family rendering through the display engine (feature 04; feature-list 4).
- `design-system`: frontend design tokens, scoped accessible accordion styling, expand-all/collapse-all control (features 05, 27; feature-list 5).
- `gutenberg-block`: the dynamic `wsfq/faq` Gutenberg block (Prebuilt mode) (feature 06; feature-list 3).
- `builder-integrations`: Elementor, Divi, Bricks, and WooCommerce Blocks integrations (features 22, 23; feature-list 3).
- `seo-schema`: Google FAQPage JSON-LD structured data output (feature 07; feature-list 10).
- `settings-dashboard`: the Gutenberg-native tabbed admin settings app and persisted settings service (feature 08).
- `product-edit-faq-panel`: the WooCommerce product-edit FAQ meta box/panel with manual entry and media support (feature 09).
- `ai-generation`: AI provider abstraction, single/multi-provider generation, failover, tone/templates, bulk generation, and AI auto-draft answers (features 10, 11, 12, 17; feature-list 7).
- `search-discovery`: inline, AJAX library and per-product search, and hash deep-linking (features 13, 14, 15; feature-list 6).
- `customer-q-a`: customer Q&A submission, moderation, email notifications, and dynamic product-attribute placeholders (feature 16; feature-list 8).
- `analytics`: FAQ engagement dashboards including objection-pattern tracking (feature 18; feature-list 9).
- `data-portability`: JSON import/export and one-click migration from competitor plugins (features 19, 20; feature-list 12).
- `rest-api`: public and admin REST API endpoints over the `wsfq/v1` namespace (feature 21; feature-list 13).
- `internationalization-multisite`: WPML/Polylang support and multisite network options with a shared FAQ library (features 24, 25; feature-list 11).

### Modified Capabilities

- None. There are no pre-existing specs; all capabilities are new.

## Impact

- Deletes repository documentation directories: `Planing/`, `docs/`.
- Root files `AGENTS.md`, `README.md`, `ARCHITECTURE.md`, `CONTRIBUTING.md` updated to reference `openspec/` instead of `docs/`/`Planing/`.
- No product code (`src/`, `tests/`, `assets/`, `blocks/`) is changed by this migration.
- Tooling: `.distignore` already excludes docs during release; `openspec/` is a dev-only artifact and never ships.