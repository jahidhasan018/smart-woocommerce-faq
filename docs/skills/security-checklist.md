---
name: security-checklist
description: Literal checklist to run before marking any PR ready — escaping, sanitization, capability checks, nonce verification, prepared SQL, no eval/extract/unserialize.
---

# Security Checklist

Run this against every PR before marking it ready. If any box is unchecked, the PR is not done.

## Input handling
- [ ] Every external input is validated (validate_callback) and sanitized (sanitize_callback).
- [ ] No `$request->get_param()` used raw.
- [ ] No `eval`, `extract`, or `unserialize` of user input anywhere.

## Output
- [ ] Every output is escaped — `esc_html` / `esc_attr` / `wp_kses_post` as appropriate.
- [ ] API keys are never logged, never echoed to the browser, never included in any REST response (write-only).

## Auth & access
- [ ] Nonce (`X-WP-Nonce`) verified for all same-origin admin-UI calls.
- [ ] Capability check (`current_user_can`) on every privileged action and every admin REST route.
- [ ] Every REST route has a real `permission_callback` — `__return_true` only if intentionally public AND rate-limited.
- [ ] AI-touching public endpoints are rate-limited by IP/user (transient-based counter).

## SQL
- [ ] Every query uses `$wpdb->prepare()` — no string concatenation of user input, no raw SQL.

## Secrets
- [ ] No secrets committed. Keys stored via `wp_options`, never logged, never exposed in responses.
- [ ] AI calls go through `wp_remote_post` (not a bundled HTTP client), so keys stay server-side.