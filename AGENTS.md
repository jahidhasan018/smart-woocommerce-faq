# AGENTS.md

## Setup
composer install
npm install
npm run env:start        # boots wp-env
npm run env:cli -- wp wc install   # WooCommerce sample data
npm run build            # compile SCSS/JS -> assets/build (after editing assets/src)

## WordPress agent-skills
This repo uses the official WordPress/agent-skills (WP plugin development, REST API, WP-CLI, PHPStan, performance, WP.org guidelines). They are installed locally and gitignored — on a fresh clone or new environment, reinstall them per README.md "Agent skills". When present, load the relevant one (e.g. wp-plugin-development) before plugin work.

**Load only what the current task needs — never all skills.** Default for any plugin work: `wp-plugin-development`. Load task-specific skills (wp-rest-api, wp-wpcli-and-ops, wp-performance, wp-plugin-directory-guidelines) only when the current feature spec calls for them.

## OpenSpec agent commands (single source)
The `/opsx-*` commands and `openspec-*` skills used to drive OpenSpec are **generated, not committed**. Their single source of truth is the openspec CLI (`openspec update`), which stamps per-tool copies into `.opencode/`, `.claude/`, `.cursor/`. Keep them gitignored; never hand-edit a per-tool copy. After a fresh clone: `openspec update` (or `openspec init` for the full profile). Verify with `openspec doctor`.

## Test
composer test:unit        # PHPUnit 10, no WP bootstrap (Brain\Monkey)
composer test:integration # PHPUnit 9.6 phar vs real WP (WP_TESTS_DIR, see bin/install-wp-tests.sh)
composer stan             # PHPStan (level defined in phpstan.neon.dist)
composer cs               # WPCS via PHP_CodeSniffer
npm run test:e2e          # Playwright against wp-env

## Integration tests (one-time setup)
bash bin/install-wp-tests.sh wordpress_test root password 127.0.0.1:54591 latest
# then:
WP_TESTS_DIR=/tmp/wsfq-wp-tests/wordpress-tests-lib composer test:integration

## Non-negotiable rules
- TDD only: failing test before implementation, every time.
- Every class implements an interface if it has more than one possible strategy (AI providers, cache backends, renderers).
- Never write a raw SQL string — always $wpdb->prepare().
- Never echo unescaped output — esc_html/esc_attr/wp_kses_post as appropriate.
- Every REST route needs a real permission_callback — never __return_true unless the endpoint is intentionally public AND rate-limited.
- Developer-friendly by default: add do_action/apply_filters hooks at every render and save point, plus every extension boundary (pre/post render, pre/post save, around AI generation, caching read/write). Prefix all hooks/filters `wsfq_` and document them in the code docblock.
- If a requirement is ambiguous or touches the data model / public API / security, STOP and ask — do not guess.

## Where things live
/src/            PSR-4, namespace WSFQ\, one class per file
/tests/Unit      fast, no WP bootstrap
/tests/Integration  runs against wp-env
/tests/e2e       Playwright
/openspec/specs/ durable behavior contract — one file per capability (features map here)
/openspec/ROADMAP.md  live status of every phase + feature — check before starting work
/src/Cli/        WP-CLI commands, namespace WSFQ\Cli\ — registered from day one
.opencode/skills/  load the relevant one before touching that area (see below)
/openspec/DECISIONS.md  why past architectural choices were made (ADRs) — check before overturning one

## WP-CLI (day one)
The plugin ships its own WP-CLI commands so agents and devs can drive it from the terminal without clicking through admin. Run them via wp-env:
npm run env:cli -- wp wsfq <subcommand>
Load .opencode/skills/wp-cli/SKILL.md for the full command list and conventions. Every command mirrors the equivalent REST/admin path and fires the same wsfq_ hooks; no command bypasses sanitization, capability, or cache-invalidation logic. Follow TDD: command tests live in tests/Cli/.

## Current phase
Check /openspec/ROADMAP.md first for what's done, in progress, or not started. It names the active phase/feature and the matching capability spec under /openspec/specs/.

Then read ONLY the matching /openspec/specs/<capability>/spec.md before starting work — do NOT read unrelated specs. Token discipline: read the smallest file that answers the task.

## Updating progress (mandatory)
OpenSpec is the single source of truth. At the START of a feature/phase, mark it `In progress` in /openspec/ROADMAP.md and create a new OpenSpec change for that feature (proposal + spec delta + tasks). When a feature/phase is DONE, update /openspec/ROADMAP.md (status → Done, add branch/notes) and archive its change BEFORE considering the work complete. Never leave progress stale.

## Canonical build plan (authoring source only)
/openspec/ is the canonical source of truth for the build plan, requirements, and status. Use it when EDITING phase/feature content. Durable requirements live in /openspec/specs/; ADRs in /openspec/DECISIONS.md; status in /openspec/ROADMAP.md. Legacy /docs/ and /Planing/ were migrated here and removed — do not reintroduce parallel doc trees.

## Git workflow
- Git repo: https://github.com/jahidhasan018/smart-woocommerce-faq
- Branches: `develop` is the default/integration branch — all features branch off it and merge back into it. `main` always reflects the last WP.org release (protected, no direct pushes). Use `feature/<short-name>` for new work, `hotfix/x.y.z` for urgent fixes off `main`.
- Commits: Conventional Commits only — `feat:`, `fix:`, `chore:`, `docs:`, `test:`. Keeps changelog generation and history predictable.
- Never commit unless explicitly asked. Before committing, inspect `git status` + `git diff`, stage only intended files, never commit secrets.