# Architecture

One diagram/paragraph per module. Updated only when architecture actually changes — not on every commit. Module layout follows `Planing/plan.md` Phase 1.8.

## Core (`src/Core/`)
`Plugin.php` singleton bootstrap wires the container and registers activation/deactivation hooks. `Activator` / `Deactivator` / `Upgrader` handle lifecycle; `Upgrader` compares the stored `wsf_db_version` option against `WSF_VERSION` and runs versioned, idempotent migrations in order. `Container.php` is a lightweight PSR-11-style container for constructor injection.

## Post Types & Taxonomies (`src/PostTypes/`, `src/Taxonomies/`)
`FaqPostType` registers the `wsf_faq` CPT. `FaqCategory` / `FaqGroup` register taxonomies. Content + assignments live as CPT + postmeta (see DECISIONS.md ADR-001).

## Admin (`src/Admin/`)
Gutenberg-native, tabbed settings dashboard (General, AI Providers, Display, Design, Advanced/Import-Export) built on `@wordpress/components`. Product-edit FAQ panel + AI generation UI mounts into the WooCommerce Product Data area. All state flows through `wsf/v1` REST routes.

## Frontend (`src/Frontend/`)
Shortcodes, blocks, and renderers for every display surface. Renderer implementations depend on `RendererInterface`. Design tokens from `assets/src/scss/tokens.scss`; frontend CSS is BEM, `wsf-` prefixed, theme-agnostic, RTL-safe.

## API (`src/Api/`)
`wsf/v1` REST namespace. Every route has a real `permission_callback` and `sanitize_callback` + `validate_callback`. Public routes are rate-limited.

## AI (`src/AI/`)
Providers (OpenAI / Gemini / Claude) implement `AiProviderInterface`; `AiGeneratorService` handles generation, tone, templates, and multi-provider failover. All calls go through `wp_remote_post` server-side; keys never reach the browser.

## Cache (`src/Cache/`)
Backends implement `CacheInterface`. WP Object Cache for per-object lists under group `wsf_faqs`; Transients for aggregate reads.

## Search / Analytics / Database (`src/Search/`, `src/Analytics/`, `src/Database/`)
Inline + AJAX search. Analytics rollups; the analytics event table (only justified custom table, see DECISIONS.md ADR-002). `src/Database/` exists only if a custom table is justified.

## Support (`src/Support/`)
Interfaces and the container that the rest of the codebase depends on.

## Data flow (high level)
Frontend/renderers and the React admin both talk to the service layer; services depend on interfaces resolved via the container; persistence is CPT/postmeta (plus the analytics table); caching sits between services and persistence.