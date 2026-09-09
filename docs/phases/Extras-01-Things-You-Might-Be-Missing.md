# Things You Might Be Missing — Additional Recommendations


- **Verify the plugin slug/name before writing code** (Phase 1.9) — "WooCommerce" leading a plugin's *name* can trigger WP.org trademark guideline pushback; confirm your naming is acceptable before you're attached to it.
- **Action Scheduler reuse.** WooCommerce already bundles Action Scheduler — use it for all background jobs (bulk AI generation, migration import) instead of adding WP-Cron-only logic or a second job-queue dependency. One less thing to maintain, one less potential version conflict with WooCommerce itself.
- **Avoid bundling an HTTP client library.** Already covered in Phase 8, but worth repeating: `wp_remote_post`/`wp_remote_get` for all AI provider calls sidesteps the entire "two plugins bundle different Guzzle versions and collide" class of bugs that trips up a lot of first-time WP.org submissions.
- **Consent/telemetry, if you ever add usage analytics for yourself (not the FAQ engagement analytics feature, but plugin-usage telemetry back to you as the developer):** WP.org requires this to be strictly opt-in with a clear consent notice — plan the toggle now if you think you'll want it later, since retrofitting consent onto default-on telemetry is a bad look.
- **Large-catalog load testing.** Test against a seeded catalog of 10,000+ products before calling any feature "done" — bulk AI generation and the display engine are the two most likely places an untested assumption ("this runs in a normal request") turns into a timeout on a real client's Black Friday catalog.
- **CONTRIBUTING.md**, even solo for now — future-you (or a hired contributor) benefits from the same onboarding doc an agent uses, and it's nearly free to write since it's largely a human-facing summary of `AGENTS.md`.
- **Don't front-load all 27 `docs/features/*.md` files.** Create each one right before you start it. A pile of stale specs for unbuilt features rots the same way stale code comments do, and contradicts the "no slop, ask when unsure" principle — an agent shouldn't be reading a six-months-stale plan for a feature whose surrounding architecture has since changed.
- **Nested `AGENTS.md` if the project grows a JS-heavy subfolder** (e.g., a future headless companion app) — the standard supports per-directory files where the closest one to the edited file wins, so you're not forced to keep cramming unrelated frontend and PHP conventions into one root file.

---

