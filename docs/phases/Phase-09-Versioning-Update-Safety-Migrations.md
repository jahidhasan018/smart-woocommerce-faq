# Phase 9 — Versioning, Update Safety & Migration System


**Skills to load:** `wp-plugin-development`, `wp-phpstan`

- Semantic Versioning strictly (`MAJOR.MINOR.PATCH`); breaking changes to hooks/filters/data shape only in a MAJOR bump, with a deprecation window (use `_deprecated_function()` / `_deprecated_hook()` for at least two MINOR versions before removal).
- Single source of truth for the version number (the plugin header `Version:` field); a small Composer/npm script syncs it into `readme.txt`'s `Stable tag` and a `WSFQ_VERSION` PHP constant at release time — never hand-edit three places separately.
- `Upgrader.php` (built in Phase 2) is the only place data-shape changes happen on update — every migration is a small, independently-testable method keyed to a version number, run in order, idempotent (safe to re-run if interrupted).
- `CHANGELOG.md` (Keep a Changelog format) updated as part of every `release/x.y.z` branch, feeding both the WP.org readme changelog and the GitHub Release notes.
- Before tagging any release: run the full Playwright e2e suite against a site pre-seeded with "previous version" data to catch upgrade regressions, not just fresh-install behavior — this is what actually protects existing client sites.

---

