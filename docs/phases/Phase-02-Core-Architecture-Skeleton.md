# Phase 2 — Core Architecture Skeleton ("walking skeleton")


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

