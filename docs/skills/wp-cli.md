---
name: wp-cli
description: The plugin's WP-CLI commands, conventions, and how to run them via wp-env. Every command mirrors the REST/admin path and fires the same wsf_ hooks.
---

# WP-CLI

The plugin ships its own WP-CLI commands from day one so agents and developers can drive it from the terminal without clicking through admin.

## Running commands
Everything runs inside `wp-env`:
```
npm run env:cli -- wp wsf <subcommand>
```
Example: `npm run env:cli -- wp wsf faq list --format=table`

## Command namespace
- All commands live under the `wsf` top-level namespace: `wp wsf <group> <command>`.
- PHP classes in `src/Cli/Commands/*`, namespace `WSF\Cli\`, registered on `WP_CLI` load.
- Grouping mirrors the plugin's domains: `faq`, `faq-category`, `faq-group`, `settings`, `ai`, `import`, `export`, `cache`.

## Conventions (non-negotiable)
- Every command calls the same service layer as the REST/admin path — never a parallel code path.
- Commands fire the same `wsf_` hooks as their REST/admin equivalents. If a hook exists for an action, the CLI command triggers it.
- Sanitization, capability checks, and cache invalidation are NEVER bypassed in a command. `--user=<id>` should be honored where capabilities matter.
- Flags/args are validated and documented via `WP_CLI::add_command()` args schema.
- Output is human-readable (tables/formatted text) and JSON-able via `--format=json` where useful.
- Follow TDD: unit tests for arg parsing/validation in `tests/Cli/` (Brain\Monkey), integration tests against a live `wp-env` instance.

## Testing
```
composer test:unit        # includes tests/Cli/ unit tests
composer test:integration # runs CLI commands against wp-env
```

## Adding a new command
1. Add the command class under `src/Cli/Commands/`, namespace `WSF\Cli\`.
2. Write the failing test first (tests/Cli/).
3. Register it in the CLI loader with an args schema.
4. Implement minimum code, delegating to the shared service layer.
5. Run `composer cs && composer stan && composer test`.
6. Update this doc with the new subcommand.