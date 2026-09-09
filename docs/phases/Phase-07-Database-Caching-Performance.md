# Phase 7 — Database, Caching & Performance


**Data model default: CPT + postmeta, not custom tables**, for the FAQ content itself and its assignments (product/category/tag/variation/global). Reasoning: you get WP core's object-cache-aware `get_post_meta()` for free, it's what WP.org reviewers expect to see, and it avoids the schema-migration complexity of custom tables for something that isn't high-volume.

**Custom table — justified only for analytics events** (Phase 3, item 18): view/expand events can be high-volume and are append-only/aggregatable, which is exactly the case where a dedicated table with `dbDelta()`-managed schema and proper indexes (on `faq_id`, `event_type`, `created_at`) outperforms postmeta. Document this tradeoff explicitly in `docs/DECISIONS.md` so no future agent "simplifies" it back into postmeta and reintroduces N+1 query problems.

**Query discipline:**
- Every custom query goes through `$wpdb->prepare()` — no exceptions, no string concatenation of user input.
- Batch operations (bulk AI generation, migration importer) use `Action Scheduler` (already bundled with WooCommerce — reuse it, don't add a second background-job library) to avoid PHP timeouts on large catalogs.
- Avoid `posts_per_page => -1` anywhere; paginate.

**Caching:**
- WP Object Cache (`wp_cache_get`/`wp_cache_set`) for per-request/per-object caching (a product's resolved FAQ list), grouped under `wsf_faqs` so it can be flushed independently of everything else.
- Transients (with sane TTLs) for expensive aggregate reads (analytics rollups, AI usage stats) — degrade gracefully to "always fresh, no persistent cache" on hosts without a persistent object cache backend.
- Explicit invalidation hooks: flush the relevant cache key/group on `save_post_wsf_faq`, `deleted_post`, `updated_postmeta` (scoped to relevant meta keys only, not every postmeta write on the site), and on product save if resolved-FAQ caching is product-scoped.
- Document the cache-key naming scheme once in `docs/skills/wordpress-core.md` so every new feature that touches caching follows the same convention.

---

