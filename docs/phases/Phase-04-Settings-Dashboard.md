# Phase 4 — Settings Dashboard (Gutenberg-native, tabbed)


**Skills to load:** `wp-plugin-development`, `wpds`, `wp-rest-api`

Matching a Jetpack-style dashboard means **using WordPress's own component library**, not hand-rolling UI:

- Build with `@wordpress/scripts` (`wp-scripts`) — this is the same toolchain Gutenberg, WooCommerce, and Jetpack itself use, so you get webpack/Babel/SCSS/React configured for you with zero custom build config, and automatic alignment with core's design language and accessibility defaults.
- Use `@wordpress/element` (WP's React wrapper — same React, no separate dependency to manage/version-conflict) + `@wordpress/components`: `TabPanel` for the tab structure, `Card`/`CardBody`, `ToggleControl`, `SelectControl`, `Notice`, `Button` — this alone gets you 90% of the way to matching the Jetpack screenshot's visual language, because you're using the exact same primitives Jetpack uses.
- One React "app" mounted into a single admin page (`admin.php?page=wsf-smart-faq`), with tabs as internal routes (no full page reloads): **General**, **AI Providers**, **Display**, **Design**, **Advanced/Import-Export**. Each tab is its own component reading/writing through the REST API (Phase 8), not `admin-post.php` form submits — keeps state management simple and testable.
- Settings persisted via the WordPress Settings API / `register_setting()` under the hood, exposed through your own `wsf/v1/settings` REST route, so the UI and any future CLI/automation both go through one validated path.
- [ ] `docs/skills/design-system.md` gets updated with real screenshots once this exists, so later features (the product-edit AI panel) visually match it automatically.

---

