---
status: done
branch: feature/08-settings-dashboard
---

## 08. Settings dashboard shell + tabs

## Goal
Ship the Gutenberg-native admin settings app (Phase 4): a React shell mounted at `admin.php?page=wsfq-smart-faq` with General / AI Providers / Display / Design / Advanced tabs. Display tab persists live settings (positions + expand-all) via a `wsfq/v1/settings` REST route backed by `register_setting()`.

## Acceptance criteria
- [x] `SettingsService` — reads/writes `wsfq_settings` option with defaults + merge-on-save; `register_setting()` sanitize callback.
- [x] `SettingsController` — `GET` + `POST` on `wsfq/v1/settings` with `manage_options` permission_callback.
- [x] `SettingsPage` — admin menu (`wsfq-smart-faq`) + `#wsfq-settings-root` mount point + `enqueue_assets()`.
- [x] React app (`assets/src/admin`) — `TabPanel` with 5 tabs; Display tab live (expand-all, one product-page location, 3 page toggles via apiFetch); other tabs are shells.
- [x] Admin assets built to `assets/build/admin.{js,css}` via webpack entry.
- [x] REST stubs (`WP_REST_Request`, `WP_REST_Response`, `WP_REST_Server`, `WP_Error`) added to unit bootstrap for testability.
- [x] Integration tests: round-trip, route registered, GET/POST, capability gating.
- [x] e2e: admin page mounts, tabs render, Display toggles appear.
- [x] `docs/skills/design-system.md` updated to note the actual settings UI.

## Open questions
None — register_setting + minimal REST confirmed; shells for unused tabs.

## Test plan
- Unit: SettingsService (3), SettingsController (4), SettingsPage (2).
- Integration: SettingsApiTest (4) — round-trip, route, GET/POST, capability.
- e2e: admin.spec — login + tabs + Display toggles.

## Decisions made while building this
- Settings persist via `register_setting()` + a `wsfq/v1/settings` REST route (Phase 4), so UI/CLI/automation share one path.
- Only display settings are live now; General/AI/Design/Advanced render shells to be filled by later features.
- Admin assets compile from `assets/src/admin/` to `assets/build/`.

## Design pass (admin settings UI)
- Fixed the admin stylesheet enqueue: it pointed at `assets/build/admin.css`, but wp-scripts emits `style-admin.css`, so no plugin CSS was loading on the settings page.
- Replaced the repeated `Card`-per-group layout with one panel surface, a tinted tab strip, and sections grouped by consequence. Rows lead with the label and trail the toggle; hairlines separate rows, space separates sections.
- Added a live "N of M on" count per section and a loading skeleton, centred empty states for the unbuilt tabs, and a tab strip that scrolls at narrow widths with no horizontal overflow.
- Replaced the no-op "Save settings" button (every toggle already auto-saved on change) with an honest "Saving changes… / All changes saved" readout, plus a designed error notice for failed saves and failed loads (with retry).
- Switched the mount from `render` to `createRoot` (removes the React 18 deprecation warning and survives React 19).
- Cut the help text under every row. Labels now describe the storefront location ("Below the short description") instead of the internal hook name, so most rows need no explanation.

## Display tab: single product location + wiring fix
Two related defects were found and fixed here:

1. **The Display tab did not control the storefront.** `SettingsService` wrote `wsfq_settings`, but `DisplayEngine` read `wsfq_display_positions` and `wsfq_expand_all` — different options. Nothing bridged them, so every toggle saved successfully and changed nothing. `DisplayEngine` now reads `SettingsService`, injected via the container.
2. **The product-page positions are mutually exclusive**, but were modelled as five independent booleans all defaulting to on, so a product page would render the accordion up to five times.

Fixes:
- `SettingsService::PRODUCT_POSITIONS` names the mutually-exclusive set. `single_product_position()` collapses it to at most one, enforced both on read (so pre-existing installs are normalised) and on save (so REST/CLI cannot create an invalid state). A stored choice outranks a default, so changing defaults never overrides a site owner's pick.
- Defaults now enable exactly one product position (`after_product_summary`); `shop_archive`, `cart`, and `checkout` stay independent and default on.
- The Display tab renders a `SelectControl` for the product location (including a "Don't show on product pages" option) and keeps toggles for the independent page-level locations.
- The `wsfq_display_positions` filter is still applied on top of the saved settings, so the documented developer escape hatch keeps working.

Verified in wp-env: POSTing all five product positions on stores exactly one; each `SelectControl` choice renders exactly one accordion on a real product page, `product_tab` renders it inside the WooCommerce tab strip, and the "don't show" option renders none.

