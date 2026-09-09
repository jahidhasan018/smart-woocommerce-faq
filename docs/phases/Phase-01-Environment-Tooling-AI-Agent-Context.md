# Phase 1 — Foundation: Environment, Tooling & AI-Agent Context System


This is the phase where you set up everything *once* so that Claude Code, Cursor, DeepSeek, or any future agent can pick up the project cold and behave correctly without you re-explaining the whole project every session.

### 1.1 Local Development Environment — `wp-env` vs Docker

**Recommendation: use `@wordpress/env` (`wp-env`) as the primary environment, not raw Docker Compose.**

Reasoning:
- `wp-env` *is* Docker under the hood — you're not giving anything up, you're getting Docker with WordPress-specific defaults already solved (WP core, correct PHP/MySQL wiring, WP-CLI baked in, xdebug toggle, multisite toggle).
- It's the exact tool the WordPress core/Gutenberg team uses to develop WordPress itself, so plugin reviewers, other contributors, and every WP-focused AI coding tool already have training data and conventions built around it.
- Config lives in one committed file, `.wp-env.json` — new agents/devs just run `npm run env:start` and get an identical environment. Zero tribal knowledge required.
- It integrates natively with `@wordpress/scripts` (`wp-scripts`) for the Gutenberg-based settings page (Phase 4) and with the WP core PHPUnit test suite for integration tests (Phase 1.5).
- You can still run multiple instances (different ports) for testing against different WP/PHP/WooCommerce version combinations by pointing `.wp-env.json` at different core/PHP versions.

**When to add plain `docker-compose.yml` on top:** only if you later need a CI matrix across many PHP versions simultaneously (e.g., PHP 7.4–8.3) in a way `wp-env` doesn't conveniently express, or you need services `wp-env` doesn't manage (e.g., a local mail-catcher, Redis for object-cache testing). Keep this as an *optional* `docker-compose.ci.yml` used only in GitHub Actions, not as your day-to-day dev environment — don't maintain two competing local setups.

**Action items:**
- [ ] `npm install --save-dev @wordpress/env`
- [ ] `.wp-env.json` — WordPress latest, WooCommerce plugin auto-installed, PHP version pinned to your minimum supported version (see 1.9)
- [ ] `.wp-env.override.json` (gitignored) for personal local tweaks (e.g., enabling `WP_DEBUG` verbosity)
- [ ] WP-CLI seed script (`bin/seed-demo-data.sh`) that creates demo products + demo FAQs, so any agent/dev gets working test data instantly

### 1.2 Git Branching Strategy (dev on GitHub → release to WP.org SVN)

WP.org plugins live in an **SVN** repository, but you develop on **GitHub**. Keep GitHub as the source of truth and automate the SVN sync only at release time.

**Branches:**
- `main` — always reflects the last version actually released to WP.org. Protected: no direct pushes, PR + passing CI required.
- `develop` — integration branch. All finished features land here first. Protected: PR + passing CI required.
- `feature/<short-name>` — one branch per item in `docs/features/`, e.g. `feature/02-display-engine`. Branches off `develop`, merges back into `develop`.
- `release/x.y.z` — cut from `develop` when preparing a release: final QA, changelog, version bump, readme.txt update. Merges into both `main` and back into `develop`.
- `hotfix/x.y.z` — branches directly off `main` for urgent post-release fixes; merges into both `main` and `develop`.

**Tagging & release automation:**
- Pushing a tag `v1.2.0` on `main` triggers a GitHub Action that:
  1. Runs the full test suite one final time (belt-and-braces).
  2. Builds a clean production zip using a `.distignore` file (strips `tests/`, `node_modules/`, `docs/`, `.github/`, dev configs).
  3. Deploys `trunk` + tags the SVN repo on WP.org automatically (use a maintained action such as `10up/action-wordpress-plugin-deploy`, updated readme `Stable tag` synced from the plugin header).
  4. Creates a GitHub Release with the built zip attached and changelog notes pulled from `CHANGELOG.md`.
- Never hand-edit the WP.org SVN directly — GitHub tags are the only path to release, so history stays consistent and reproducible.

**Action items:**
- [ ] Create `develop` branch, set as default branch for day-to-day PRs
- [ ] Branch protection rules on `main` and `develop` (required status checks: phpcs, phpstan, phpunit, playwright-smoke)
- [ ] `.distignore` file
- [ ] `.github/workflows/deploy.yml` (tag-triggered SVN deploy)
- [ ] Conventional Commits enforced (`feat:`, `fix:`, `chore:`, `docs:`, `test:`) — makes changelog generation and agent-authored commit messages predictable

### 1.3 AI-Agent Context System (the core of what you asked for)

The goal: **one canonical, hand-written source of truth that every agent reads first**, plus **on-demand "skills" the agent loads only when relevant**, so you never re-explain the project and never burn tokens re-reading the whole codebase every session.

**`AGENTS.md` at the repo root.** This is now a real, widely-adopted open standard (Linux Foundation–stewarded, MIT-licensed) natively read by Claude Code, Cursor, Codex, Copilot, and 20+ other tools <cite index="7-1,5-1">it's a plain-Markdown, no-required-fields file that explains a project to agents the way a README explains it to humans, covering build commands, test commands, code style, and architectural decisions</cite>. Critically: <cite index="9-1">keep it hand-written and under ~150 lines — LLM-generated or overly generic AGENTS.md files measurably hurt agent performance, so only include project-specific facts an agent couldn't infer on its own</cite>. Don't restate "write clean code" — the model already knows that. Do include:

```markdown
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
/docs/skills/    load the relevant one before touching that area (see below)
/docs/features/  status of every feature — check before starting work
/docs/DECISIONS.md  why past architectural choices were made — check before overturning one

## Current phase
See /docs/PROGRESS.md for what's done, in progress, or not started.
```

That's it — short, concrete, commands-first. Everything else lives in linked docs the agent opens only when the task needs them, which keeps token usage low on unrelated tasks.

**Tool-specific pointer files** (each just a one-liner, so you're not maintaining duplicate instructions):
- `CLAUDE.md` → `Read /AGENTS.md first. Then read the relevant file(s) under /docs/skills/ for this task.`
- `.cursor/rules/main.mdc` (or `.cursorrules`) → same one-liner.
- `.github/copilot-instructions.md` → same one-liner.

**`docs/skills/` — reusable, on-demand playbooks.** This maps directly onto Anthropic's **Agent Skills** format (`SKILL.md`: a short frontmatter + Markdown body), which <cite index="6-1">was originally developed at Anthropic, released as an open standard, and has since been adopted by Claude Code, OpenAI Codex, Cursor, VS Code, and 30+ other tools</cite>. The distinction from `AGENTS.md` matters: <cite index="6-1">AGENTS.md is project-scoped context; skills are reusable, portable capabilities the agent loads on demand</cite>. Build these:

- `docs/skills/wordpress-core.md` — which WP APIs to use for what (Settings API vs custom REST, Transients API vs Object Cache API, `WP_Query` conventions, hook naming conventions, when to use `dbDelta()` vs postmeta).
- `docs/skills/wordpress-plugin-standards.md` — WPCS rules that actually get enforced, plugin header format, activation/deactivation/uninstall hook responsibilities, the security checklist (nonce → capability → sanitize → escape, every single time).
- `docs/skills/php-oop-solid.md` — how SOLID applies specifically in this codebase, with a real example class from `/src/` as the reference pattern for new classes.
- `docs/skills/testing-tdd.md` — the red-green-refactor loop expected here, which test type (unit vs integration vs e2e) covers what, mocking conventions (Brain\Monkey for WP functions in unit tests).
- `docs/skills/security-checklist.md` — a literal checklist the agent runs through before marking any PR ready: escaping, sanitization, capability checks, nonce verification, SQL prepared statements, no `eval`/`extract`, no unserialized user input.
- `docs/skills/design-system.md` — points at Phase 6's design tokens, states explicitly "match existing components before creating new ones," includes screenshots of the existing UI so a new session doesn't invent a visually inconsistent settings panel.

**`docs/DECISIONS.md` — lightweight Architecture Decision Records.** One entry per non-obvious choice ("Why postmeta instead of a custom table for FAQ assignments" / "Why `wp_remote_post` instead of Guzzle for AI calls"). This is what lets a brand-new agent understand *why* something looks the way it does instead of "helpfully" refactoring it back to a worse pattern.

**Action items:**
- [ ] `AGENTS.md` (root)
- [ ] `CLAUDE.md`, `.cursor/rules/main.mdc`, `.github/copilot-instructions.md` (pointer stubs)
- [ ] `docs/skills/*.md` (the six above, to start)
- [ ] `docs/DECISIONS.md`
- [ ] `docs/ARCHITECTURE.md` — one diagram/paragraph per module, updated only when architecture actually changes

### 1.4 Progress-Tracking System

- `docs/PROGRESS.md` — a single table, always current:

  | # | Feature | Status | Branch | Notes |
  |---|---|---|---|---|
  | 01 | Core CPT + taxonomy | Not started | — | — |

  Status values: `Not started / In progress / Blocked / Needs tests / Done`.
- `docs/features/NN-feature-name.md` — one file per feature, created *before* work starts:
  ```markdown
  ---
  status: not-started
  branch: feature/02-display-engine
  ---
  ## Goal
  ## Acceptance criteria
  ## Open questions (agent fills this in, you answer, agent removes once resolved)
  ## Test plan
  ## Decisions made while building this
  ```
- Every PR description links to its `docs/features/NN-*.md` file and updates its status.

This is what makes switching agents mid-project cheap: a new session reads `AGENTS.md` → `docs/PROGRESS.md` → the one relevant `docs/features/NN-*.md` file, and has full context in three small reads instead of re-scanning the whole repo.

### 1.5 Coding Standards & Static Analysis

- [ ] `composer.json` — PHP `>=7.4` in the header for broad host compatibility, developed/tested against 8.1–8.3.
- [ ] `.editorconfig`
- [ ] **PHP_CodeSniffer** with `WordPress-Extra` + `WordPress-Docs` + `PHPCompatibilityWP` rulesets (`phpcs.xml.dist`) — this is what WP.org reviewers themselves run against your code.
- [ ] **PHPStan** with `szepeviktor/phpstan-wordpress` + WooCommerce stubs (`phpstan.neon.dist`). Start at level 5, ratchet up as the codebase matures — record the current level in `docs/DECISIONS.md` so agents don't "fix" it downward to make errors disappear.
- [ ] `declare(strict_types=1);` in every PHP file under `/src`.
- [ ] Composer scripts: `composer cs`, `composer cs:fix`, `composer stan`, `composer test`.
- [ ] A pre-push git hook (simple shell script, no heavy tooling needed) running `composer cs && composer stan && composer test:unit` — fast local feedback before CI even runs.

### 1.6 Testing Infrastructure (scaffolding only — TDD starts writing real tests in Phase 2)

- **Unit tests:** PHPUnit + `Brain\Monkey` (mocks WP core functions, no WP bootstrap needed → fast, runs in milliseconds, good for pure logic like the AI prompt builder or cache key generation).
- **Integration tests:** PHPUnit against the real WP core test suite, running inside `wp-env` (needed for anything touching `$wpdb`, post types, hooks firing end-to-end).
- **E2E tests:** Playwright using `@wordpress/e2e-test-utils-playwright` (the official WP package) — covers admin settings flows, the product-edit FAQ panel, and frontend accordion rendering/interaction.
- [ ] `phpunit.xml.dist` (two test suites: `unit`, `integration`)
- [ ] `tests/bootstrap.php`
- [ ] `playwright.config.ts` pointed at the `wp-env` URL
- [ ] A "hello world" test in each layer, committed and green, before any real feature work starts — proves the pipeline actually works end-to-end.

### 1.7 CI/CD Pipeline Skeleton

`.github/workflows/ci.yml`, triggered on PR to `develop`/`main`:
1. `composer install`, `npm ci`
2. `composer cs` (fail fast, cheapest check)
3. `composer stan`
4. `composer test:unit`
5. Boot `wp-env` in CI (MySQL service container) → `composer test:integration`
6. `npm run build` (compiles SCSS/JS via `wp-scripts`)
7. `npm run test:e2e` (Playwright against the booted `wp-env` instance)

Matrix: PHP 7.4 / 8.1 / 8.3 × WordPress latest / latest-1, WooCommerce latest. Merges to `develop`/`main` blocked until all green.

`.github/workflows/deploy.yml` — tag-triggered SVN release, described in 1.2.

### 1.8 Project Skeleton (modular, PSR-4)

```
smart-woocommerce-faq/
├── smart-woocommerce-faq.php     # header + bootstrap only, no logic
├── uninstall.php
├── composer.json / composer.lock
├── package.json
├── phpcs.xml.dist / phpstan.neon.dist / phpunit.xml.dist
├── playwright.config.ts
├── .wp-env.json
├── .distignore
├── readme.txt                     # WP.org listing
├── CHANGELOG.md
├── AGENTS.md / CLAUDE.md / .cursor/
├── .github/workflows/{ci,deploy}.yml
├── docs/
│   ├── ARCHITECTURE.md
│   ├── DECISIONS.md
│   ├── PROGRESS.md
│   ├── skills/*.md
│   └── features/NN-*.md
├── src/                            # namespace WSF\
│   ├── Core/            Plugin.php, Activator.php, Deactivator.php, Upgrader.php
│   ├── Cli/             Commands/* (e.g. FaqCommand.php, SettingsCommand.php, ImportCommand.php)
│   ├── PostTypes/       FaqPostType.php
│   ├── Taxonomies/      FaqCategory.php, FaqGroup.php
│   ├── Admin/           SettingsPage.php, Tabs/*, MetaBoxes/FaqMetaBox.php
│   ├── Frontend/        Shortcodes/*, Blocks/*, Renderers/*
│   ├── Api/             RestController.php, Controllers/*
│   ├── AI/              Providers/{OpenAi,Gemini,Claude}Provider.php, AiGeneratorService.php
│   ├── Cache/           CacheManager.php
│   ├── Search/
│   ├── Analytics/
│   ├── Database/        (only if a custom table is truly justified — see Phase 7)
│   └── Support/         Interfaces/, Container.php
├── tests/{Unit,Integration,e2e,Cli}/
├── assets/src/{scss,js}/
├── assets/build/                   # gitignored, compiled output
└── languages/
```

- [ ] `composer.json` autoload: `"WSF\\": "src/"`
- [ ] Lightweight service container in `Core/Container.php` (no need for a heavy DI framework — a simple PSR-11-compatible container is enough) so classes receive dependencies via constructor injection, never `new SomeConcreteClass()` buried inside another class.
- [ ] `src/Cli/` WP-CLI scaffold — a `wsf` command namespace registered on `WP_CLI` load, plus `tests/Cli/` test harness (Brain\Monkey for command args, integration for live wp-env runs). See `/docs/skills/wp-cli.md`.

### 1.9 Pre-Flight WP.org Compliance Checks (do these *before* investing months of work)

- [ ] **Check plugin slug availability** on WP.org — "Smart FAQ" style names are common; confirm `smart-woocommerce-faq` isn't taken and doesn't collide with an existing trademark. The slug leads with "smart", not "WooCommerce", which already avoids WP.org's trademark-guideline restriction on trademarked names at the start of a plugin name/slug (see display-name guidance in feature-list.md).
- [ ] Pick a GPL-compatible license (GPLv2 or later) — required for WP.org.
- [ ] Decide the text domain now (must match the slug) and use it consistently from day one — retrofitting i18n later is painful.
- [ ] Read WP.org's guideline on external HTTP requests: since this plugin calls OpenAI/Gemini/Claude, this **must** be disclosed (readme "Additional Information" section + an in-plugin notice) and must be strictly opt-in/BYOK (which your plan already is — good).

---

