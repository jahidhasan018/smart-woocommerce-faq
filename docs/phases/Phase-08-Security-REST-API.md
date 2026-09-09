# Phase 8 — Security & REST API


- Namespace: `wsf/v1`.
- **Every** route has a real `permission_callback` — `current_user_can()` checks for admin routes, and for the handful of genuinely public routes (e.g., a read-only public FAQ endpoint for headless frontends), rate-limit by IP/user via a transient-based counter to prevent scraping/cost abuse on any endpoint that touches AI generation.
- `args` schema on every registered route with both `sanitize_callback` and `validate_callback` — never trust `$request->get_param()` raw.
- Nonce (`X-WP-Nonce`) verification for all same-origin admin-UI calls (handled automatically if you use `apiFetch` from `@wordpress/api-fetch`, which is exactly what your Phase 4/5 React panels should use).
- AI provider calls go through `wp_remote_post`, **not** a bundled HTTP client library (Guzzle, etc.) — this avoids the classic WP.org dependency-conflict problem entirely (no need for namespace-prefixing tools like Strauss/Mozart) and automatically respects the site's proxy settings, HTTP filters, and timeout conventions.
- API keys stored via `wp_options` with values passed through WP's encryption-at-rest only if the host supports it; at minimum, never log them, never expose them in any REST response (write-only field), never send them to the browser.
- Full pass of `docs/skills/security-checklist.md` before any PR touching input handling or output rendering is marked done.

---

