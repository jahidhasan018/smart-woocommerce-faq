# WooCommerce Smart FAQ — Full Build Plan

**Repo:** https://github.com/jahidhasan018/smart-woocommerce-faq
**Approach:** TDD + SOLID + OOP, feature-by-feature, WP.org submission target
**Code prefix:** `wsf_` (functions/hooks/options), `WSF\` (PHP namespace), `wsf-` (CSS/JS/slugs)

---

## 0. Guiding Principles (apply to every phase below)

1. **TDD is non-negotiable.** For every unit of behavior: write a failing test → write the minimum code to pass → refactor. No PHP class ships without a corresponding test file. An agent that writes implementation code before a test is violating the plan.
2. **SOLID + OOP throughout.** Every class has one reason to change. Depend on interfaces (`AiProviderInterface`, `CacheInterface`, `RendererInterface`), not concrete classes. No god classes, no static-everything utility dumps.
3. **Ask, don't assume.** If a requirement, data shape, UX behavior, or naming decision is ambiguous, the agent stops and asks you — it does not guess and it does not silently pick "a reasonable default" for anything architectural (data model, hook names, public API shape, security-sensitive logic). Cosmetic micro-decisions (a variable name, whitespace) don't need to block on this.
4. **No slop code.** Every function has a docblock. Every external input is validated/sanitized. Every output is escaped. No commented-out code left in commits. No "TODO: fix later" merged into `develop`.
5. **Small, reviewable units.** One feature = one branch = one PR = one docs/features file = passing CI before merge.
6. **Developer-friendly by default.** Add `do_action`/`apply_filters` hooks at every render and save point, plus every extension boundary: pre/post render, pre/post save, around AI generation, caching read/write, and import/export. Prefix all hooks/filters `wsf_` and document each in its code docblock. Agents write a hook *as they build the code*, never as a retrofitted "documentation pass" — retrofitting is the exact rework TDD-for-UI is meant to avoid.
7. **CLI first.** Every feature ships a mirrored WP-CLI command (namespace `WSF\Cli\`) from day one, so agents and developers can drive the plugin from the terminal. CLI commands reuse the same service layer as the REST/admin paths, fire the same `wsf_` hooks, and never bypass sanitization, capability, or cache-invalidation logic.

---

## Phase 1 — Foundation: Environment, Tooling & AI-Agent Context System

**Skills to load:** `wp-project-triage`, `wp-plugin-development`, `wp-wpcli-and-ops`, `wp-phpstan`, `wp-plugin-directory-guidelines`

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

## Phase 2 — Core Architecture Skeleton ("walking skeleton")

**Skills to load:** `wp-plugin-development`, `wp-wpcli-and-ops`

Build the thinnest possible vertical slice that proves the whole toolchain works, entirely test-first:

- [ ] `Plugin.php` — singleton bootstrap, wires the container, registers activation/deactivation hooks. **Test first**, then implement.
- [ ] `Activator.php` / `Deactivator.php` / `uninstall.php` — with a settings toggle "Remove all data on uninstall" (default off) so client sites don't silently lose data.
- [ ] `Upgrader.php` — compares stored `wsf_db_version` option against `WSF_VERSION` constant on `plugins_loaded`, runs versioned migration methods sequentially. This exists from day one, even with nothing to migrate yet, so the pattern is established before real data model changes happen (this is the mechanism that keeps future updates from breaking existing client sites — see Phase 9).
- [ ] `FaqPostType.php` — register `wsf_faq` CPT (empty, no meta fields yet).
- [ ] `FaqCategory.php` / `FaqGroup.php` — register taxonomies.
- [ ] `Duplicate/clone FAQ` — `wsf_clone_faq` helper that copies an FAQ post + its metadata/assignments (used by the library and product-edit UI; wired fully in feature 02). Expose via a documented `wsf_` hook so cloning can be extended or suppressed.
- [ ] First green run of the full CI pipeline (cs, stan, unit, integration, e2e-smoke) on a real PR.
- [ ] Update `docs/PROGRESS.md`.

---

## Phase 3 — Feature Build Loop

**Skills to load:** `wp-plugin-development` (base for every feature) + the feature-specific skill(s) below

Every feature from here on follows the same loop, one at a time, never in parallel:

1. Create `docs/features/NN-name.md`, fill in Goal + Acceptance Criteria.
2. Branch `feature/NN-name` off `develop`.
3. Write failing tests (unit first, integration where needed).
4. Implement the minimum code to pass.
5. Refactor for SOLID compliance.
6. Run `composer cs && composer stan && composer test`.
7. Add/update Playwright e2e coverage if it's user-facing.
8. Update `docs/features/NN-name.md` (status → Done, note any decisions) and `docs/PROGRESS.md`.
9. Open PR → CI green → merge to `develop`.

**Recommended feature order** (mapped from your feature list — build order optimizes for "each layer unblocks the next" and lets you demo something real early):

| # | Feature | Depends on |
|---|---|---|
| 01 | Core CPT/taxonomy skeleton | Phase 2 |
| 02 | FAQ assignment (product / category / tag / variation / global) — also wire the duplicate/clone helper from Phase 2 into the library and product-edit UI | 01 |
| 03 | Display engine: hooks for all product/shop/cart/checkout positions | 02 |
| 04 | Shortcodes (`[wsf_all]`, `[wsf_product]`, etc.) | 03 |
| 05 | Design system + base accordion styles (Phase 6) — includes the expand-all / collapse-all control | 03 |
| 06 | Gutenberg block (Prebuilt + Custom) | 04, 05 |
| 07 | Google FAQPage JSON-LD schema | 03 |
| 08 | Settings dashboard shell + tabs (Phase 4) | 05 |
| 09 | Product-edit FAQ meta box, manual entry (no AI yet) — supports media in answers (images, video, embedded HTML); includes optional comments-on-FAQs toggle | 02, 08 |
| 10 | AI provider abstraction + single-provider generation (Phase 5) | 09 |
| 11 | Multi-provider + failover, tone/templates | 10 |
| 12 | Bulk AI generation (category-wide) via Action Scheduler | 11 |
| 13 | Inline search | 04 |
| 14 | AJAX library + per-product search | 13 |
| 15 | Hash deep-linking | 04 |
| 16 | Customer Q&A submission + moderation — includes email notifications on new question / new answer and dynamic product-attribute placeholders (`{product_price}`, `{stock_status}`) in answers | 09 |
| 17 | AI auto-draft answers for customer questions | 10, 16 |
| 18 | FAQ engagement analytics | 03 |
| 19 | Import/export (JSON) | 01–08 stable |
| 20 | Migration importer from competitor plugins | 19 |
| 21 | REST API (public + admin) | 08 |
| 22 | Elementor / Divi / Bricks integrations — Bricks is a deliberate differentiator competitors don't offer; call it out in marketing/readme | 06 |
| 23 | WooCommerce Blocks (Cart/Checkout block templates) compatibility | 03 |
| 24 | WPML / Polylang | stable core |
| 25 | Multisite network options — includes the network-wide shared FAQ library option | stable core |
| 26 | Developer hooks/filters documentation pass | all above |
| 27 | Accessibility + RTL polish pass | all display work |

Each row becomes its own `docs/features/NN-*.md` file, created only when you're about to start it (don't pre-write 27 empty specs — that's busywork, not planning).

**Feature → skill map** (load only the relevant one, on top of `wp-plugin-development`):

| Feature(s) | Skill(s) |
|---|---|
| 05, 06, 08, 22, 23, 27 (frontend UI, blocks, editors) | `wp-block-development`, `wpds` |
| 07 (schema), 14, 16, 17 (AJAX/search/Q&A) | `wp-rest-api` |
| 21 (REST API) | `wp-rest-api` |
| 10, 11, 12 (AI providers/generation) | `wp-rest-api`, `wp-phpstan` |
| 18 (analytics) | `wp-performance` |
| 19, 20 (import/export/migration) | `wp-wpcli-and-ops`, `wp-plugin-development` |
| 24 (WPML/Polylang), 25 (multisite) | `wp-wpcli-and-ops` |
| Any feature with WP-CLI commands | `wp-wpcli-and-ops` |
| All (final QA) | `wp-plugin-directory-guidelines`, `wp-performance` |

---

## Phase 4 — Settings Dashboard (Gutenberg-native, tabbed)

**Skills to load:** `wp-plugin-development`, `wpds`, `wp-rest-api`

Matching a Jetpack-style dashboard means **using WordPress's own component library**, not hand-rolling UI:

- Build with `@wordpress/scripts` (`wp-scripts`) — this is the same toolchain Gutenberg, WooCommerce, and Jetpack itself use, so you get webpack/Babel/SCSS/React configured for you with zero custom build config, and automatic alignment with core's design language and accessibility defaults.
- Use `@wordpress/element` (WP's React wrapper — same React, no separate dependency to manage/version-conflict) + `@wordpress/components`: `TabPanel` for the tab structure, `Card`/`CardBody`, `ToggleControl`, `SelectControl`, `Notice`, `Button` — this alone gets you 90% of the way to matching the Jetpack screenshot's visual language, because you're using the exact same primitives Jetpack uses.
- One React "app" mounted into a single admin page (`admin.php?page=wsf-smart-faq`), with tabs as internal routes (no full page reloads): **General**, **AI Providers**, **Display**, **Design**, **Advanced/Import-Export**. Each tab is its own component reading/writing through the REST API (Phase 8), not `admin-post.php` form submits — keeps state management simple and testable.
- Settings persisted via the WordPress Settings API / `register_setting()` under the hood, exposed through your own `wsf/v1/settings` REST route, so the UI and any future CLI/automation both go through one validated path.
- [ ] `docs/skills/design-system.md` gets updated with real screenshots once this exists, so later features (the product-edit AI panel) visually match it automatically.

---

## Phase 5 — Product-Edit Screen: FAQ Panel + AI Generation

**Skills to load:** `wp-plugin-development`, `wp-rest-api`, `wp-phpstan`

- A React panel (same `@wordpress/components` toolkit) mounted into the WooCommerce Product Data metabox area via `woocommerce_product_data_panels` / `woocommerce_product_data_tabs`.
- Manual mode: add/edit/reorder FAQs inline (drag-and-drop via `@wordpress/components`' built-in sortable primitives or a small dependency-free implementation).
- AI mode: "Generate with AI" button → tone selector → objection-buster template dropdown → calls `wsf/v1/ai/generate` REST endpoint → renders editable preview cards → "Insert" commits selected FAQs to the product.
- All AI calls happen **server-side** (PHP → provider API) via `wp_remote_post`, never client-side — so API keys never touch the browser.
- Every AI-generated FAQ is clearly marked as such until a human edits/approves it (protects data quality and gives you an analytics signal later: AI-accepted vs AI-rejected suggestions).
- Manual-mode answers support media — images, video, and embedded HTML — via the core media picker; AI-generated answers stay plain-text until a human inserts media.

---

## Phase 6 — Design System

**Skills to load:** `wpds`, `wp-block-development`

- Use **`@wordpress/scripts`' built-in Sass support** (import `.scss` directly from your JS entry points — no extra Sass toolchain to configure or maintain) for both admin and frontend styles. This is "something that already ships with the WordPress ecosystem," as you asked for.
- `assets/src/scss/tokens.scss` — CSS custom properties (`--wsf-color-primary`, `--wsf-radius`, `--wsf-space-*`, `--wsf-font-*`), generated once from Sass variables. Runtime customization (colors chosen in Settings → Design tab) writes an inline `<style>` block overriding these custom properties — no rebuild needed for a client to pick a brand color.
- Two separate stylesheets, never one mega-file:
  - `admin.scss` — deliberately inherits WP admin's own design tokens where possible (don't fight `wp-admin` styling).
  - `frontend.scss` — fully scoped (BEM, `wsf-` prefixed classes), theme-agnostic, mobile-first, Flexbox for the accordion rows, CSS Grid for the multi-column layout option, logical properties (`margin-inline`, etc.) for RTL support.
- Accessibility baked in from the start: `aria-expanded`, `aria-controls`, keyboard operability (Enter/Space toggles, arrow-key navigation between questions) — not a "polish pass" afterthought, since retrofitting ARIA into already-built markup is exactly the kind of rework TDD-for-UI is meant to avoid.
- Expand-all / collapse-all control — a frontend toggle that expands/collapses every FAQ on the page; exposed as a render option and via a `wsf_` hook so it can be positioned or hidden.

---

## Phase 7 — Database, Caching & Performance

**Skills to load:** `wp-performance`, `wp-plugin-development`

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

## Phase 8 — Security & REST API

**Skills to load:** `wp-rest-api`, `wp-plugin-development`

- Namespace: `wsf/v1`.
- **Every** route has a real `permission_callback` — `current_user_can()` checks for admin routes, and for the handful of genuinely public routes (e.g., a read-only public FAQ endpoint for headless frontends), rate-limit by IP/user via a transient-based counter to prevent scraping/cost abuse on any endpoint that touches AI generation.
- `args` schema on every registered route with both `sanitize_callback` and `validate_callback` — never trust `$request->get_param()` raw.
- Nonce (`X-WP-Nonce`) verification for all same-origin admin-UI calls (handled automatically if you use `apiFetch` from `@wordpress/api-fetch`, which is exactly what your Phase 4/5 React panels should use).
- AI provider calls go through `wp_remote_post`, **not** a bundled HTTP client library (Guzzle, etc.) — this avoids the classic WP.org dependency-conflict problem entirely (no need for namespace-prefixing tools like Strauss/Mozart) and automatically respects the site's proxy settings, HTTP filters, and timeout conventions.
- API keys stored via `wp_options` with values passed through WP's encryption-at-rest only if the host supports it; at minimum, never log them, never expose them in any REST response (write-only field), never send them to the browser.
- Full pass of `docs/skills/security-checklist.md` before any PR touching input handling or output rendering is marked done.

---

## Phase 9 — Versioning, Update Safety & Migration System

**Skills to load:** `wp-plugin-development`, `wp-phpstan`

- Semantic Versioning strictly (`MAJOR.MINOR.PATCH`); breaking changes to hooks/filters/data shape only in a MAJOR bump, with a deprecation window (use `_deprecated_function()` / `_deprecated_hook()` for at least two MINOR versions before removal).
- Single source of truth for the version number (the plugin header `Version:` field); a small Composer/npm script syncs it into `readme.txt`'s `Stable tag` and a `WSF_VERSION` PHP constant at release time — never hand-edit three places separately.
- `Upgrader.php` (built in Phase 2) is the only place data-shape changes happen on update — every migration is a small, independently-testable method keyed to a version number, run in order, idempotent (safe to re-run if interrupted).
- `CHANGELOG.md` (Keep a Changelog format) updated as part of every `release/x.y.z` branch, feeding both the WP.org readme changelog and the GitHub Release notes.
- Before tagging any release: run the full Playwright e2e suite against a site pre-seeded with "previous version" data to catch upgrade regressions, not just fresh-install behavior — this is what actually protects existing client sites.

---

## Phase 10 — WP.org Submission Readiness

**Skills to load:** `wp-plugin-directory-guidelines`, `wp-plugin-development`

- [ ] `readme.txt` complete: description, installation, FAQ, screenshots, changelog, "Additional Information" disclosing third-party AI API usage.
- [ ] No obfuscated/minified-only code without a build step + source available (your build step already handles this since compiled assets come from committed source).
- [ ] All bundled third-party code GPL-compatible and disclosed.
- [ ] Full `phpcs` (WordPress-Extra) pass with zero errors, warnings justified/documented if suppressed.
- [ ] Uninstall behavior tested: default (data preserved) and opt-in (full cleanup) both verified via integration test.
- [ ] Privacy: since AI features send product titles/descriptions to third-party APIs, add a short privacy notice pointing to each provider's policy, and make sure it's clearly opt-in (no API key configured = feature fully dormant, no outbound calls).

---

## Phase 11 — Post-Launch Operations

**Skills to load:** `wp-plugin-directory-guidelines`, `wp-wpcli-and-ops`

- Support forum monitoring cadence (WP.org support forum) — decide upfront how often you'll check it; unanswered threads hurt your rating and review-team standing for future plugins.
- `hotfix/*` branch process (Phase 1.2) exercised for the first real production bug — treat it as a fire drill early rather than the first time under pressure.
- Marketing/analytics future-proofing: since you explicitly don't want a premium tier now, still design the extension points (interfaces, hook names, a documented `wsf_extensions` filter) so a future companion add-on (or even a totally separate premium plugin) could integrate without you rewriting core — you don't have to build it, just don't architecturally block it.
- Revisit `docs/DECISIONS.md` quarterly; anything that's aged into "we'd do this differently now" gets a new decision entry, not a silent rewrite.

---

## Things You Might Be Missing — Additional Recommendations

- **Verify the plugin slug/name before writing code** (Phase 1.9) — "WooCommerce" leading a plugin's *name* can trigger WP.org trademark guideline pushback; confirm your naming is acceptable before you're attached to it.
- **Action Scheduler reuse.** WooCommerce already bundles Action Scheduler — use it for all background jobs (bulk AI generation, migration import) instead of adding WP-Cron-only logic or a second job-queue dependency. One less thing to maintain, one less potential version conflict with WooCommerce itself.
- **Avoid bundling an HTTP client library.** Already covered in Phase 8, but worth repeating: `wp_remote_post`/`wp_remote_get` for all AI provider calls sidesteps the entire "two plugins bundle different Guzzle versions and collide" class of bugs that trips up a lot of first-time WP.org submissions.
- **Consent/telemetry, if you ever add usage analytics for yourself (not the FAQ engagement analytics feature, but plugin-usage telemetry back to you as the developer):** WP.org requires this to be strictly opt-in with a clear consent notice — plan the toggle now if you think you'll want it later, since retrofitting consent onto default-on telemetry is a bad look.
- **Large-catalog load testing.** Test against a seeded catalog of 10,000+ products before calling any feature "done" — bulk AI generation and the display engine are the two most likely places an untested assumption ("this runs in a normal request") turns into a timeout on a real client's Black Friday catalog.
- **CONTRIBUTING.md**, even solo for now — future-you (or a hired contributor) benefits from the same onboarding doc an agent uses, and it's nearly free to write since it's largely a human-facing summary of `AGENTS.md`.
- **Don't front-load all 27 `docs/features/*.md` files.** Create each one right before you start it. A pile of stale specs for unbuilt features rots the same way stale code comments do, and contradicts the "no slop, ask when unsure" principle — an agent shouldn't be reading a six-months-stale plan for a feature whose surrounding architecture has since changed.
- **Nested `AGENTS.md` if the project grows a JS-heavy subfolder** (e.g., a future headless companion app) — the standard supports per-directory files where the closest one to the edited file wins, so you're not forced to keep cramming unrelated frontend and PHP conventions into one root file.

---

## Appendix — Key Dependencies

**Composer (require-dev unless noted):**
`phpunit/phpunit`, `brain/monkey`, `wp-coding-standards/wpcs`, `phpcompatibility/phpcompatibility-wp`, `szepeviktor/phpstan-wordpress`, `phpstan/phpstan`, `yoast/phpunit-polyfills`

**npm (devDependencies):**
`@wordpress/env`, `@wordpress/scripts`, `@wordpress/element`, `@wordpress/components`, `@wordpress/api-fetch`, `@wordpress/i18n`, `@wordpress/e2e-test-utils-playwright`, `@playwright/test`

**Runtime PHP:** none beyond WordPress/WooCommerce core APIs — deliberately zero bundled runtime dependencies, to avoid the conflict/prefixing problem entirely.