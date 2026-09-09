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
- [x] React app (`assets/src/admin`) — `TabPanel` with 5 tabs; Display tab live (expand-all + 8 position toggles via apiFetch); other tabs are shells.
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