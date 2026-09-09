# Smart WooCommerce FAQ

A developer-friendly WooCommerce FAQ plugin: central FAQ library, product/category/tag/variation assignment, display engine, Gutenberg blocks, AI generation (BYOK), search, analytics, and more. Built with TDD + SOLID + OOP for WP.org distribution.

> **Status:** In planning/foundation. See `docs/PROGRESS.md` for current state.

## Setup

Requires Node.js, npm, Composer, and Docker.

```bash
composer install
npm install
npm run env:start        # boots wp-env (WordPress + WooCommerce in Docker)
npm run env:cli -- wp wc install   # WooCommerce sample data
```

## Test

```bash
composer test:unit        # PHPUnit, no WP bootstrap (Brain\Monkey)
composer test:integration # PHPUnit against live wp-env instance
composer stan             # PHPStan (level defined in phpstan.neon.dist)
composer cs               # WPCS via PHP_CodeSniffer
npm run test:e2e          # Playwright against wp-env
```

## WP-CLI

The plugin ships its own WP-CLI commands. Run them via wp-env:

```bash
npm run env:cli -- wp wsf <subcommand>
```

See `docs/skills/wp-cli.md` for the command list and conventions.

## Agent skills (WordPress/agent-skills)

The project uses the official [WordPress/agent-skills](https://github.com/WordPress/agent-skills) repository to give AI coding assistants (Claude, Cursor, Codex, VS Code/Copilot, etc.) expert-level WordPress knowledge.

**These skills are intentionally NOT committed to this repo.** They are installed locally and gitignored (`.claude/skills/`, `.codex/`, `.cursor/skills/`, `.github/skills/`). After a fresh clone, or in a new dev environment, reinstall them with the steps below.

### Installed skills

- `wordpress-router` — classifies the repo and routes to the right workflow
- `wp-project-triage` — detects project type, tooling, and versions
- `wp-plugin-development` — plugin architecture, hooks, Settings API, security
- `wp-block-development` — Gutenberg blocks: `block.json`, attributes, rendering, deprecations
- `wp-rest-api` — REST API routes, schema, auth, response shaping
- `wp-wpcli-and-ops` — WP-CLI commands and automation
- `wp-performance` — profiling, caching, database optimization
- `wp-phpstan` — PHPStan config, baselines, WP-specific typing
- `wp-plugin-directory-guidelines` — WordPress Plugin Directory Guidelines
- `wpds` — WordPress Design System

### Install (one-time, per environment)

```bash
# 1. Clone the skills repo somewhere outside this project (e.g. /tmp)
git clone https://github.com/WordPress/agent-skills.git
cd agent-skills

# 2. Build the distribution
node shared/scripts/skillpack-build.mjs --clean

# 3. Install the skills into this repo (project-scoped, gitignored)
node shared/scripts/skillpack-install.mjs \
  --dest=<path/to/smart-woocommerce-faq> \
  --targets=claude,cursor,codex,vscode \
  --skills=wordpress-router,wp-project-triage,wp-plugin-development,wp-block-development,wp-rest-api,wp-wpcli-and-ops,wp-performance,wp-phpstan,wp-plugin-directory-guidelines,wpds
```

Or install globally so they're available across all your projects:

```bash
node shared/scripts/skillpack-install.mjs --global
# or, for a specific set:
node shared/scripts/skillpack-install.mjs --global --skills=wp-plugin-development,wp-rest-api,wp-wpcli-and-ops
```

To see all available skills: `node shared/scripts/skillpack-install.mjs --list`

### Project-scoped vs global

- **Project-scoped** (the default above) installs into `.claude/skills/`, `.codex/`, `.cursor/skills/`, `.github/skills/` inside this repo. They're gitignored here on purpose.
- **Global** installs to `~/.claude/skills/`, `~/.cursor/skills/`, etc., available in every repo.
- When a skill exists in both scopes, the project-level version wins.

## Docs

- `docs/PROGRESS.md` — current status of every feature and build phase
- `docs/phases/` — one file per build phase (read only the active one)
- `docs/features/` — one file per feature (created pre-build)
- `docs/skills/` — this project's own agent skills
- `docs/DECISIONS.md` — architecture decision records
- `docs/ARCHITECTURE.md` — module map
- `AGENTS.md` — instructions for AI coding assistants (read this first)

## Contributing

See `CONTRIBUTING.md`.

## License

GPL-2.0-or-later (for WP.org distribution).