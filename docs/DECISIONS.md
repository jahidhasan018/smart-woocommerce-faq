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
PHPStan starts at level 5 and ratchets up as the codebase matures. The current level is recorded here so agents don't "fix" it downward to make errors disappear. Current: **level 5**.

## ADR-006 — Code prefix `wsfq` (4 chars, NOT `wsf`)
WPCS's `PrefixAllGlobals` requires a minimum 4-char prefix; the original `wsf` (3 chars) fails it and isn't configurable. Renamed the whole project (`wsf` → `wsfq`) across hooks, REST routes, options, namespace (`WSFQ\`), WP-CLI (`wp wsfq`), and all docs in one pass. The plan.md and all phase/feature docs now use `wsfq`. Do NOT revert to a 3-char prefix.

## ADR-007 — Two PHPUnit versions: 10 for unit, 9.6 for integration
The WP core test library (even trunk) still calls PHPUnit 9-only APIs (`PHPUnit\Util\Test::parseTestMethodAnnotations`). So: unit tests run on **PHPUnit 10.5** (via composer, fast pure-logic), integration tests run on a **PHPUnit 9.6 phar** (downloaded by `bin/install-wp-tests.sh`, run via `phpunit.integration.xml.dist` + `bin/run-integration-tests.sh`). Do not "simplify" integration back onto the composer PHPUnit.

## ADR-008 — PSR-4 + `WordPress.Files.FileName` excluded
The plugin uses PSR-4 (one class per file, filename = class name), which contradicts WPCS's legacy `class-` / hyphenated filename rules. `WordPress.Files.FileName` error codes are excluded in `phpcs.xml.dist` (justified for PSR-4 projects). `EscapeOutput.ExceptionNotEscaped` is also excluded — exception messages are never echoed.

## ADR-009 — WordPress agent-skills are gitignored
`WordPress/agent-skills` skills are installed project-scoped (`.claude/skills/`, `.codex/`, `.cursor/skills/`, `.github/skills/`) but gitignored — the repo stays clean and skills are reinstalled per README "Agent skills". Do NOT commit the skill folders.

## ADR-010 — Minimal `phpstan-wc.php` stub instead of full WooCommerce stubs
`php-stubs/woocommerce-stubs` is 4.5MB / 145k lines and crashes PHPStan even at 2G memory. We declare only the WC symbols the plugin references (`WC_Product`, `WC_Cart`, `WC()`) in `phpstan-wc.php` (bootstrap-loaded for analysis only). If the plugin later uses more WC API surface, extend that stub rather than re-adding the full package.

## Open decision slots
- Plugin slug/name on WP.org must be verified before code (see Phase 1.9). Decided: slug `smart-woocommerce-faq`, display name "Smart FAQ for WooCommerce" — verified free on WP.org.
- Text domain: `smart-woocommerce-faq` (matches slug) — set in the plugin header.