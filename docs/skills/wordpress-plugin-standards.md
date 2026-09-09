---
name: wordpress-plugin-standards
description: WPCS rules that get enforced, plugin header format, activation/deactivation/uninstall hook responsibilities, and the security checklist for WordPress plugins.
---

# WordPress Plugin Standards

The rules WP.org reviewers actually enforce. Follow these in every PHP file.

## Plugin header format
Header must be present and well-formed. See the plan's project skeleton for the header fields.

## WPCS (WordPress-Extra + WordPress-Docs + PHPCompatibilityWP)
Run via `composer cs`. Zero errors allowed on merge. Warnings must be justified/documented if suppressed.

## Activation / deactivation / uninstall responsibilities
- `Activator.php` / `Deactivator.php` — phase-scoped setup/teardown only; never bloat them with long-running work.
- `uninstall.php` — a settings toggle "Remove all data on uninstall" (default off) governs whether data is preserved. Test both paths (Phase 10).

## Security checklist (nonce → capability → sanitize → escape, every time)
1. Nonce verification for all same-origin admin-UI calls.
2. Capability checks before any privileged action.
3. Sanitize every external input.
4. Escape every output.
5. SQL through `$wpdb->prepare()`.
6. No `eval`/`extract`/`unserialize` of user input.

## Strict types
Every file under `/src/` declares `declare(strict_types=1);`.