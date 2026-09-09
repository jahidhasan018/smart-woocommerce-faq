# Architecture Decision Records

One entry per non-obvious choice. A new entry explains *why* something looks the way it does, so an agent doesn't "helpfully" refactor it back to a worse pattern. Revisit quarterly; anything that's aged into "we'd do this differently now" gets a *new* entry, never a silent rewrite.

## ADR-000 — Plan layout: verbatim per-phase copies
`Planing/plan.md` is the single source of truth for the build plan. `docs/phases/` holds one verbatim copy per phase. Never edit `docs/phases/` without also editing the source plan and `docs/PROGRESS.md`. Rationale: the plan's own instructions and the AGENTS.md convention require the plan to be canonical and unsummarized.

## ADR-001 — Why postmeta instead of a custom table for FAQ assignments (planned)
FAQ content and its assignments (product/category/tag/variation/global) live as CPT + postmeta, not a custom table. Benefits: WP core's object-cache-aware `get_post_meta()` for free, aligns with WP.org reviewer expectations, and avoids schema-migration complexity for a non-high-volume dataset.

## ADR-002 — Why a custom table IS justified for analytics events (planned)
Analytics events (view/expand) are high-volume, append-only, and aggregatable — a dedicated table with `dbDelta()`-managed schema and indexes on (`faq_id`, `event_type`, `created_at`) outperforms postmeta. Do NOT "simplify" this back into postmeta; it reintroduces N+1 query problems.

## ADR-003 — Why `wp_remote_post` instead of Guzzle for AI calls (planned)
`wp_remote_post`/`wp_remote_get` sidesteps the classic WP.org dependency-conflict problem entirely (no two-plugins-collide class of bugs, no need for namespace prefixing via Strauss/Mozart), respects the site's proxy settings, HTTP filters, and timeout conventions, and keeps keys server-side.

## ADR-004 — Action Scheduler reuse (planned)
Bulk AI generation and migration import run through Action Scheduler (already bundled with WooCommerce) rather than WP-Cron-only logic or a second background-job library. One less dependency, no potential conflict with WooCommerce itself.

## ADR-005 — PHPStan level (planned)
PHPStan starts at level 5 and ratchets up as the codebase matures. The current level is recorded here so agents don't "fix" it downward to make errors disappear.

## Open decision slots
- Plugin slug/name on WP.org must be verified before code (see Phase 1.9) — a trademark-compliant display name vs slug decision.
- Text domain must be chosen to match the final slug.