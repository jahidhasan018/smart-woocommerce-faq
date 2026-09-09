# AGENTS.md

## Setup
composer install
npm install
npm run env:start        # boots wp-env
npm run env:cli -- wp wc install   # WooCommerce sample data

## Test
composer test:unit        # PHPUnit, no WP bootstrap (Brain\Monkey)
composer test:integration # PHPUnit against live wp-env instance
composer stan             # PHPStan (level defined in phpstan.neon.dist)
composer cs               # WPCS via PHP_CodeSniffer
npm run test:e2e          # Playwright against wp-env

## Non-negotiable rules
- TDD only: failing test before implementation, every time.
- Every class implements an interface if it has more than one possible strategy (AI providers, cache backends, renderers).
- Never write a raw SQL string — always $wpdb->prepare().
- Never echo unescaped output — esc_html/esc_attr/wp_kses_post as appropriate.
- Every REST route needs a real permission_callback — never __return_true unless the endpoint is intentionally public AND rate-limited.
- If a requirement is ambiguous or touches the data model / public API / security, STOP and ask — do not guess.

## Where things live
/src/            PSR-4, namespace WSF\, one class per file
/tests/Unit      fast, no WP bootstrap
/tests/Integration  runs against wp-env
/tests/e2e       Playwright
/docs/phases/    one file per build phase — read ONLY the phase you're working on (see Current phase)
/docs/features/  status of every feature — check before starting work
/docs/skills/    load the relevant one before touching that area (see below)
/docs/DECISIONS.md  why past architectural choices were made — check before overturning one

## Current phase
Check /docs/PROGRESS.md first for what's done, in progress, or not started. It names the active phase/feature.

Then read ONLY the matching /docs/phases/*.md file for that phase — do NOT read the whole plan. Token discipline: read the smallest file that answers the task.

## Updating progress (mandatory)
- At the START of a feature/phase, mark it `In progress` in /docs/PROGRESS.md (and its docs/features file, if one exists).
- When a feature/phase is DONE, update /docs/PROGRESS.md (status → Done, add branch/notes) and the matching docs/features file BEFORE considering the work complete. Never leave progress stale.
- Same rule applies to phase status changes in /docs/phases/.

## Canonical build plan (authoring source only)
Planing/plan.md is the write-only source of truth — use it when EDITING phase content, never read it in full during normal work. Each phase has its own working copy in /docs/phases/. Never edit /docs/phases/ without also updating /docs/PROGRESS.md and the source plan.

## Git workflow
- Git repo: https://github.com/jahidhasan018/smart-woocommerce-faq
- Branches: `develop` is the default/integration branch — all features branch off it and merge back into it. `main` always reflects the last WP.org release (protected, no direct pushes). Use `feature/<short-name>` for new work, `hotfix/x.y.z` for urgent fixes off `main`.
- Commits: Conventional Commits only — `feat:`, `fix:`, `chore:`, `docs:`, `test:`. Keeps changelog generation and history predictable.
- Never commit unless explicitly asked. Before committing, inspect `git status` + `git diff`, stage only intended files, never commit secrets.