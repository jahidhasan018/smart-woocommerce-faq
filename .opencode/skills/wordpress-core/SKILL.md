---
name: wordpress-core
description: Which WordPress core APIs to use for what — Settings API vs custom REST, Transients vs Object Cache, WP_Query conventions, hook naming, dbDelta vs postmeta.
---

# WordPress Core API Playbook

Which WP core API to reach for, per task. Read this before writing any WP-facing code.

## Settings vs custom REST
- Settings stored through the WP Settings API / `register_setting()` where they back admin UI.
- Expose them to the React settings app through the plugin's own `wsfq/v1/settings` REST route (see Phase 8) so UI and automation share one validated path.

## Transients vs Object Cache
- **WP Object Cache** (`wp_cache_get`/`wp_cache_set`) for per-request/per-object caching (a product's resolved FAQ list). Grouped under `wsfq_faqs` so it can be flushed independently.
- **Transients** (with sane TTLs) for expensive aggregate reads (analytics rollups, AI usage stats). Degrade gracefully to "always fresh" on hosts without a persistent object cache backend.

## Cache-key naming scheme
Follow the convention documented here (and in `openspec/DECISIONS.md`). Name cache keys with the `wsfq_` prefix and scope them to what they cache, e.g. product-scoped resolved-FAQ keys. Any new feature that touches caching must follow this convention.

## Query conventions
- Every custom query goes through `$wpdb->prepare()` — no exceptions, no string concatenation of user input.
- Avoid `posts_per_page => -1`; paginate.

## dbDelta vs postmeta
- FAQ content and assignments = CPT + postmeta by default.
- A custom table via `dbDelta()` is justified only for high-volume append-only analytics events (see Phase 7).

## Hook naming
Prefix all hooks/filters with `wsfq_` (e.g. `wsfq_before_render`, `wsfq_faq_saved`). See plan.md "Code prefix" conventions.

## Hooks & filters everywhere (developer-friendly mandate)
This plugin is developer-friendly by default. Add a hook at every render and save point, and at every extension boundary — not as a retrofitted documentation pass:
- Pre/post render: `wsfq_before_render`, `wsfq_after_render` (filters on the rendered output/args).
- Pre/post save: `wsfq_before_save_faq`, `wsfq_faq_saved` (post ID + data as args).
- Around AI generation: pre-generate filter (provider, prompt, tone, model) and post-generate action (result, accepted/rejected signal for analytics).
- Caching read/write: filter the resolved FAQ list before caching, action after cache write/invalidate.
- Import/export: filter on the exported payload, action after import commits.
- CLI commands fire the SAME hooks as their REST/admin equivalents — never a parallel, hookless code path.
Every hook is documented in its code docblock with `@since`, params, and return type.